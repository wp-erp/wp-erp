<?php
/**
 * WP-ERP HR — OAuth sign-in for MCP clients.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Lets an AI app connect by signing in, instead of pasting a token.
 *
 * Apps that run in a browser or on a phone — Claude on the web is the usual
 * one — have nowhere to paste a bearer token. They follow the MCP
 * authorization flow instead: discover this server's metadata, register
 * themselves, send the person to a consent screen, and exchange the code
 * that comes back for a token.
 *
 * The token issued at the end is an ordinary MCP token, so access rules,
 * the audit log and revocation all work exactly as they do for a token
 * created by hand. It differs only in that it expires and is renewed with
 * a refresh token.
 *
 * Every client is a public client: there is no client secret, and PKCE
 * (S256) is mandatory.
 */
class OAuth {

	/**
	 * The nonce action on the consent screen.
	 */
	const AUTHORIZE_ACTION = 'erp_hr_mcp_authorize';

	/**
	 * The path of the consent screen, under the site's home URL.
	 *
	 * It is not a wp-login.php action: some hosts and security plugins send
	 * anyone who is already signed in away from wp-login.php, whatever the
	 * action, and the consent screen would never be seen.
	 */
	const AUTHORIZE_PATH = '/erp-hr-mcp/authorize';

	/**
	 * Prefix on a client id.
	 */
	const CLIENT_PREFIX = 'erp_hr_mcp_client_';

	/**
	 * How long an authorization code may wait to be exchanged.
	 */
	const CODE_TTL = 600;

	/**
	 * Registrations one address may make per hour.
	 */
	const REGISTRATIONS_PER_HOUR = 20;

	/**
	 * Days an unused client registration is kept.
	 */
	const STALE_CLIENT_DAYS = 30;

	/**
	 * The token issuer.
	 *
	 * @var TokenManager
	 */
	private $tokens;

	/**
	 * Constructor.
	 *
	 * @param TokenManager $tokens The token issuer.
	 */
	public function __construct( TokenManager $tokens ) {
		$this->tokens = $tokens;
	}

	/**
	 * The registered-clients table.
	 *
	 * @return string
	 */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'erp_hr_mcp_oauth_clients';
	}

	/**
	 * The issuer identifier: the site's home URL.
	 *
	 * @return string
	 */
	public static function issuer(): string {
		return untrailingslashit( home_url() );
	}

	/**
	 * Where a client finds the protected-resource metadata. Sent with every
	 * 401 so a client never has to guess it.
	 *
	 * @return string
	 */
	public static function resource_metadata_url(): string {
		return home_url( '/.well-known/oauth-protected-resource' );
	}

	/**
	 * The consent screen URL.
	 *
	 * @return string
	 */
	public static function authorize_url(): string {
		return home_url( self::AUTHORIZE_PATH );
	}

	/**
	 * The authorization endpoint advertised to clients.
	 *
	 * It lives beside the token and registration endpoints and only forwards
	 * to the consent screen. It has no query string of its own, because some
	 * clients build the sign-in link by adding "?" and their parameters to
	 * this address as plain text.
	 *
	 * @return string
	 */
	public static function authorization_endpoint(): string {
		return rest_url( Settings::REST_NAMESPACE . Settings::REST_ROUTE . '/oauth/authorize' );
	}

	/**
	 * Hook the parts that do not live in REST: the well-known documents and
	 * the consent screen.
	 *
	 * @return void
	 */
	public function register(): void {
		// The HR module boots during init, so that hook has already passed.
		add_action( 'parse_request', [ $this, 'maybe_serve_metadata' ], 0 );
		add_action( 'parse_request', [ $this, 'maybe_serve_authorize' ], 0 );
	}

	/**
	 * Register the client-registration and token endpoints.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Settings::REST_NAMESPACE,
			Settings::REST_ROUTE . '/oauth/authorize',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'forward_authorize' ],
				// Open by design: it only forwards to the consent screen,
				// which asks the person to sign in.
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			Settings::REST_NAMESPACE,
			Settings::REST_ROUTE . '/oauth/register',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'handle_register' ],
				// Open by design: a client registers before anyone has
				// signed in. A registration grants nothing by itself.
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			Settings::REST_NAMESPACE,
			Settings::REST_ROUTE . '/oauth/token',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'handle_token' ],
				// Authenticated by the code or refresh token in the body.
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Forward an authorization request to the consent screen.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function forward_authorize( WP_REST_Request $request ): WP_REST_Response {
		$args = [];

		foreach ( [ 'response_type', 'client_id', 'redirect_uri', 'code_challenge', 'code_challenge_method', 'state', 'scope', 'resource' ] as $key ) {
			$value = $request->get_param( $key );

			if ( is_string( $value ) && '' !== $value ) {
				$args[ $key ] = rawurlencode( $value );
			}
		}

		$response = new WP_REST_Response( null, 302 );
		$response->header( 'Location', add_query_arg( $args, self::authorize_url() ) );

		return $this->no_store( $response );
	}

	/**
	 * Serve the discovery documents.
	 *
	 * These have to answer at fixed paths under the site root, which REST
	 * cannot provide, so the request is recognised by its path before
	 * WordPress goes looking for a page to show.
	 *
	 * @return void
	 */
	public function maybe_serve_metadata(): void {
		$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );

		if ( false === strpos( $path, '/.well-known/' ) ) {
			return;
		}

		// On a site installed in a sub-directory the documents are looked
		// for relative to that directory.
		$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );

		if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
			$path = substr( $path, strlen( untrailingslashit( $home ) ) );
		}

		if ( 0 === strpos( $path, '/.well-known/oauth-protected-resource' ) ) {
			$document = $this->resource_metadata();
		} elseif ( 0 === strpos( $path, '/.well-known/oauth-authorization-server' ) || 0 === strpos( $path, '/.well-known/openid-configuration' ) ) {
			$document = $this->server_metadata();
		} else {
			return;
		}

		if ( ! Settings::get()['enabled'] ) {
			return;
		}

		nocache_headers();
		header( 'Access-Control-Allow-Origin: *' );
		wp_send_json( $document );
	}

	/**
	 * Serve the consent screen when its path is requested.
	 *
	 * @return void
	 */
	public function maybe_serve_authorize(): void {
		$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
		$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );

		if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
			$path = substr( $path, strlen( untrailingslashit( $home ) ) );
		}

		if ( self::AUTHORIZE_PATH !== untrailingslashit( $path ) ) {
			return;
		}

		nocache_headers();
		$this->handle_authorize();
		exit;
	}

	/**
	 * Handle the consent screen: show it, and act on the answer.
	 *
	 * @return void
	 */
	public function handle_authorize(): void {
		if ( ! Settings::get()['enabled'] ) {
			wp_die( esc_html__( 'The WP ERP HR MCP server is not enabled on this site.', 'erp' ), '', [ 'response' => 404 ] );
		}

		// phpcs:disable WordPress.Security.NonceVerification -- The request is read here; the nonce is checked before anything is granted.
		$params = [];

		foreach ( [ 'response_type', 'client_id', 'redirect_uri', 'code_challenge', 'code_challenge_method', 'state', 'scope', 'resource' ] as $key ) {
			$params[ $key ] = isset( $_REQUEST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) ) : '';
		}

		// A redirect URI is compared exactly, so it must not be altered.
		$params['redirect_uri'] = isset( $_REQUEST['redirect_uri'] ) ? trim( (string) wp_unslash( $_REQUEST['redirect_uri'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// phpcs:enable WordPress.Security.NonceVerification

		$client = $this->find_client( $params['client_id'] );

		// Until the client and its redirect URI are known to be genuine
		// there is nowhere safe to send an error, so it is shown here.
		if ( ! $client || ! in_array( $params['redirect_uri'], $client['redirect_uris'], true ) ) {
			wp_die( esc_html__( 'This sign-in request is not valid: the app is not registered with this site, or its return address does not match. Try connecting again from the app.', 'erp' ), '', [ 'response' => 400 ] );
		}

		if ( 'code' !== $params['response_type'] ) {
			$this->redirect_back( $params, [ 'error' => 'unsupported_response_type' ] );
		}

		if ( '' === $params['code_challenge'] || 'S256' !== $params['code_challenge_method'] ) {
			$this->redirect_back(
				$params,
				[
					'error'             => 'invalid_request',
					'error_description' => 'PKCE with the S256 method is required.',
				]
			);
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( add_query_arg( array_map( 'rawurlencode', array_filter( $params, 'strlen' ) ), self::authorize_url() ) ) );
			exit;
		}

		$user_id = get_current_user_id();
		$allowed = Access::can_connect( $user_id );
		$post    = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];

		if ( $post ) {
			check_admin_referer( self::AUTHORIZE_ACTION );

			if ( ! $allowed || empty( $_POST['approve'] ) ) {
				$this->redirect_back( $params, [ 'error' => 'access_denied' ] );
			}

			$scope = isset( $_POST['token_scope'] ) && 'read' === $_POST['token_scope'] ? 'read' : 'full';
			$code  = bin2hex( random_bytes( 32 ) );

			set_transient(
				self::code_key( $code ),
				[
					'client_id'      => $client['client_id'],
					'client_name'    => $client['name'],
					'user_id'        => $user_id,
					'redirect_uri'   => $params['redirect_uri'],
					'code_challenge' => $params['code_challenge'],
					'scope'          => $scope,
				],
				self::CODE_TTL
			);

			$this->redirect_back( $params, [ 'code' => $code ] );
		}

		$this->render_consent( $client, $params, $allowed );
		exit;
	}

	/**
	 * Register a client (RFC 7591).
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_register( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;

		if ( ! Settings::get()['enabled'] ) {
			return $this->oauth_error( 'not_found', 'The MCP server is not enabled on this site.', 404 );
		}

		// Anyone can call this, so one address cannot fill the table.
		$throttle = 'erp_hr_mcp_reg_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		$attempts = (int) get_transient( $throttle );

		if ( $attempts >= self::REGISTRATIONS_PER_HOUR ) {
			return $this->oauth_error( 'too_many_requests', 'Too many registrations. Try again later.', 429 );
		}

		set_transient( $throttle, $attempts + 1, HOUR_IN_SECONDS );

		$body = json_decode( $request->get_body(), true );
		$uris = is_array( $body ) && isset( $body['redirect_uris'] ) && is_array( $body['redirect_uris'] ) ? array_values( $body['redirect_uris'] ) : [];

		if ( empty( $uris ) || count( $uris ) > 10 ) {
			return $this->oauth_error( 'invalid_redirect_uri', 'Between one and ten redirect_uris are required.' );
		}

		foreach ( $uris as $uri ) {
			if ( ! is_string( $uri ) || ! self::is_acceptable_redirect( $uri ) ) {
				return $this->oauth_error( 'invalid_redirect_uri', 'Each redirect URI must use https, a loopback http address, or an app-specific scheme.' );
			}
		}

		$name = isset( $body['client_name'] ) && is_string( $body['client_name'] ) ? sanitize_text_field( $body['client_name'] ) : '';
		$name = '' === $name ? __( 'AI app', 'erp' ) : mb_substr( $name, 0, 100 );

		$client_id = self::CLIENT_PREFIX . bin2hex( random_bytes( 16 ) );
		$inserted  = $wpdb->insert(
			self::table(),
			[
				'client_id'     => $client_id,
				'name'          => $name,
				'redirect_uris' => wp_json_encode( $uris ),
				'created_at'    => current_time( 'mysql', true ),
			],
			[ '%s', '%s', '%s', '%s' ]
		);

		if ( ! $inserted ) {
			return $this->oauth_error( 'server_error', 'The client could not be registered.', 500 );
		}

		return $this->no_store(
			new WP_REST_Response(
				[
					'client_id'                  => $client_id,
					'client_id_issued_at'        => time(),
					'client_name'                => $name,
					'redirect_uris'              => $uris,
					'grant_types'                => [ 'authorization_code', 'refresh_token' ],
					'response_types'             => [ 'code' ],
					'token_endpoint_auth_method' => 'none',
				],
				201
			)
		);
	}

	/**
	 * Exchange a code, or a refresh token, for an access token.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_token( WP_REST_Request $request ): WP_REST_Response {
		if ( ! Settings::get()['enabled'] ) {
			return $this->oauth_error( 'not_found', 'The MCP server is not enabled on this site.', 404 );
		}

		$client_id = (string) $request->get_param( 'client_id' );
		$grant     = (string) $request->get_param( 'grant_type' );

		if ( ! $this->find_client( $client_id ) ) {
			return $this->oauth_error( 'invalid_client', 'Unknown client.', 401 );
		}

		if ( 'refresh_token' === $grant ) {
			$issued = $this->tokens->refresh( (string) $request->get_param( 'refresh_token' ), $client_id );

			if ( ! $issued ) {
				return $this->oauth_error( 'invalid_grant', 'The refresh token is not valid.' );
			}

			return $this->token_response( $issued );
		}

		if ( 'authorization_code' !== $grant ) {
			return $this->oauth_error( 'unsupported_grant_type', 'Only authorization_code and refresh_token are supported.' );
		}

		$code = (string) $request->get_param( 'code' );
		$key  = self::code_key( $code );
		$data = '' === $code ? false : get_transient( $key );

		// A code is good once, whatever happens next.
		delete_transient( $key );

		if ( ! is_array( $data )
			|| ! hash_equals( (string) $data['client_id'], $client_id )
			|| ! hash_equals( (string) $data['redirect_uri'], (string) $request->get_param( 'redirect_uri' ) )
		) {
			return $this->oauth_error( 'invalid_grant', 'The authorization code is not valid.' );
		}

		$challenge = rtrim( strtr( base64_encode( hash( 'sha256', (string) $request->get_param( 'code_verifier' ), true ) ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		if ( ! hash_equals( (string) $data['code_challenge'], $challenge ) ) {
			return $this->oauth_error( 'invalid_grant', 'The code verifier does not match.' );
		}

		$issued = $this->tokens->create_grant( (int) $data['user_id'], (string) $data['client_name'], (string) $data['scope'], $client_id );

		if ( is_wp_error( $issued ) ) {
			return $this->oauth_error( 'access_denied', $issued->get_error_message() );
		}

		$this->touch_client( $client_id );

		return $this->token_response( $issued );
	}

	/**
	 * Drop registrations that never led to a connection.
	 *
	 * @return void
	 */
	public static function prune_clients(): void {
		global $wpdb;

		$clients = self::table();
		$tokens  = TokenManager::table();

		$wpdb->query(
			$wpdb->prepare(
				"DELETE c FROM {$clients} c LEFT JOIN {$tokens} t ON t.client_id = c.client_id
				WHERE t.id IS NULL AND c.created_at < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				gmdate( 'Y-m-d H:i:s', time() - self::STALE_CLIENT_DAYS * DAY_IN_SECONDS )
			)
		);
	}

	/**
	 * Whether a URI may be registered as a place to send people back to.
	 *
	 * @param string $uri The redirect URI.
	 *
	 * @return bool
	 */
	private static function is_acceptable_redirect( string $uri ): bool {
		if ( strlen( $uri ) > 2000 || preg_match( '/[\s<>"\']/', $uri ) ) {
			return false;
		}

		$parts  = wp_parse_url( $uri );
		$scheme = isset( $parts['scheme'] ) ? strtolower( $parts['scheme'] ) : '';

		if ( '' === $scheme || isset( $parts['fragment'] ) ) {
			return false;
		}

		if ( 'https' === $scheme ) {
			return ! empty( $parts['host'] );
		}

		// Plain http only to the person's own machine, where a desktop app
		// listens for the answer.
		if ( 'http' === $scheme ) {
			return isset( $parts['host'] ) && in_array( strtolower( $parts['host'] ), [ 'localhost', '127.0.0.1', '[::1]' ], true );
		}

		// An app-specific scheme such as cursor://, but nothing a browser
		// would run or read from disk.
		return ! in_array( $scheme, [ 'javascript', 'data', 'vbscript', 'file', 'blob', 'about' ], true );
	}

	/**
	 * Look a client up.
	 *
	 * @param string $client_id The client id.
	 *
	 * @return array{client_id: string, name: string, redirect_uris: string[]}|null
	 */
	private function find_client( string $client_id ) {
		global $wpdb;

		if ( 0 !== strpos( $client_id, self::CLIENT_PREFIX ) ) {
			return null;
		}

		$table = self::table();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT client_id, name, redirect_uris FROM {$table} WHERE client_id = %s", $client_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		if ( ! $row ) {
			return null;
		}

		return [
			'client_id'     => (string) $row->client_id,
			'name'          => (string) $row->name,
			'redirect_uris' => array_values( array_filter( (array) json_decode( (string) $row->redirect_uris, true ), 'is_string' ) ),
		];
	}

	/**
	 * Note that a client just completed a sign-in.
	 *
	 * @param string $client_id The client id.
	 *
	 * @return void
	 */
	private function touch_client( string $client_id ): void {
		global $wpdb;

		$wpdb->update(
			self::table(),
			[ 'last_used_at' => current_time( 'mysql', true ) ],
			[ 'client_id' => $client_id ],
			[ '%s' ],
			[ '%s' ]
		);
	}

	/**
	 * Where an authorization code is kept until it is exchanged.
	 *
	 * @param string $code The plaintext code.
	 *
	 * @return string
	 */
	private static function code_key( string $code ): string {
		return 'erp_hr_mcp_code_' . hash_hmac( 'sha256', $code, wp_salt( 'auth' ) );
	}

	/**
	 * Send the person back to the app, with a code or an error.
	 *
	 * Only ever called once the redirect URI is known to be registered.
	 *
	 * @param array<string, string> $params The authorization request.
	 * @param array<string, string> $args   What to hand back.
	 *
	 * @return void
	 */
	private function redirect_back( array $params, array $args ): void {
		if ( '' !== $params['state'] ) {
			$args['state'] = $params['state'];
		}

		$args['iss'] = self::issuer();

		// The registered URI may use an app's own scheme, which the "safe"
		// redirect would refuse.
		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		wp_redirect( add_query_arg( array_map( 'rawurlencode', $args ), $params['redirect_uri'] ) );
		exit;
	}

	/**
	 * The protected-resource metadata (RFC 9728).
	 *
	 * @return array<string, mixed>
	 */
	private function resource_metadata(): array {
		return [
			'resource'                 => Settings::endpoint_url(),
			'authorization_servers'    => [ self::issuer() ],
			'bearer_methods_supported' => [ 'header' ],
			'scopes_supported'         => TokenManager::SCOPES,
			'resource_name'            => 'WP ERP HR',
		];
	}

	/**
	 * The authorization-server metadata (RFC 8414).
	 *
	 * @return array<string, mixed>
	 */
	private function server_metadata(): array {
		$base = rest_url( Settings::REST_NAMESPACE . Settings::REST_ROUTE . '/oauth' );

		return [
			'issuer'                                         => self::issuer(),
			'authorization_endpoint'                         => self::authorization_endpoint(),
			'token_endpoint'                                 => $base . '/token',
			'registration_endpoint'                          => $base . '/register',
			'response_types_supported'                       => [ 'code' ],
			'grant_types_supported'                          => [ 'authorization_code', 'refresh_token' ],
			'code_challenge_methods_supported'               => [ 'S256' ],
			'token_endpoint_auth_methods_supported'          => [ 'none' ],
			'scopes_supported'                               => TokenManager::SCOPES,
			'authorization_response_iss_parameter_supported' => true,
		];
	}

	/**
	 * Shape a successful token reply.
	 *
	 * @param array<string, mixed> $issued What TokenManager minted.
	 *
	 * @return WP_REST_Response
	 */
	private function token_response( array $issued ): WP_REST_Response {
		return $this->no_store(
			new WP_REST_Response(
				[
					'access_token'  => $issued['token'],
					'token_type'    => 'Bearer',
					'expires_in'    => $issued['expires_in'],
					'refresh_token' => $issued['refresh_token'],
					'scope'         => $issued['scope'],
				],
				200
			)
		);
	}

	/**
	 * An OAuth error reply.
	 *
	 * @param string $error       The error code.
	 * @param string $description What went wrong.
	 * @param int    $status      The HTTP status.
	 *
	 * @return WP_REST_Response
	 */
	private function oauth_error( string $error, string $description, int $status = 400 ): WP_REST_Response {
		return $this->no_store(
			new WP_REST_Response(
				[
					'error'             => $error,
					'error_description' => $description,
				],
				$status
			)
		);
	}

	/**
	 * Keep a reply that carries credentials out of every cache.
	 *
	 * @param WP_REST_Response $response The reply.
	 *
	 * @return WP_REST_Response
	 */
	private function no_store( WP_REST_Response $response ): WP_REST_Response {
		$response->header( 'Cache-Control', 'no-store' );
		$response->header( 'Pragma', 'no-cache' );

		return $response;
	}

	/**
	 * Print the consent screen.
	 *
	 * @param array<string, mixed>  $client  The client asking.
	 * @param array<string, string> $params  The authorization request.
	 * @param bool                  $allowed Whether this person may connect at all.
	 *
	 * @return void
	 */
	private function render_consent( array $client, array $params, bool $allowed ): void {
		$user = wp_get_current_user();
		$host = (string) wp_parse_url( $params['redirect_uri'], PHP_URL_HOST );
		$host = '' === $host ? (string) wp_parse_url( $params['redirect_uri'], PHP_URL_SCHEME ) . '://' : $host;

		status_header( 200 );
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		header( 'X-Frame-Options: DENY' );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<meta name="robots" content="noindex, nofollow" />
			<meta name="referrer" content="no-referrer" />
			<title><?php echo esc_html( __( 'Connect an AI app', 'erp' ) . ' ‹ ' . get_bloginfo( 'name' ) ); ?></title>
			<?php wp_print_styles( [ 'login' ] ); ?>
			<style>#login input[type="radio"]:checked::before { background-color: #3858e9; }</style>
		</head>
		<body class="login wp-core-ui">
		<div id="login">
		<h1 style="font-size:20px;font-weight:600;text-align:center;margin-bottom:24px;"><?php esc_html_e( 'Connect an AI app', 'erp' ); ?></h1>
		<form method="post" action="<?php echo esc_url( self::authorize_url() ); ?>">
			<?php wp_nonce_field( self::AUTHORIZE_ACTION ); ?>
			<?php foreach ( $params as $key => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<?php endforeach; ?>

			<p>
				<?php
				printf(
					/* translators: 1: the app's name, 2: the site's name. */
					esc_html__( '%1$s wants to connect to HR on %2$s.', 'erp' ),
					'<strong>' . esc_html( $client['name'] ) . '</strong>',
					'<strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>'
				);
				?>
			</p>
			<p style="margin-top:12px;">
				<?php
				printf(
					/* translators: 1: the signed-in person's name, 2: where the app will be sent the result. */
					esc_html__( 'You are signed in as %1$s. The app returns to %2$s.', 'erp' ),
					'<strong>' . esc_html( $user->display_name ) . '</strong>',
					'<code>' . esc_html( $host ) . '</code>'
				);
				?>
			</p>

			<?php if ( $allowed ) : ?>
				<p style="margin-top:12px;">
					<?php esc_html_e( 'It will act as you: it can see and do only what you can in HR, limited to what your administrator allows AI apps to use.', 'erp' ); ?>
				</p>
				<p style="margin-top:16px;">
					<label>
						<input type="radio" name="token_scope" value="full" checked="checked" />
						<?php esc_html_e( 'Everything I can access', 'erp' ); ?>
					</label><br />
					<label>
						<input type="radio" name="token_scope" value="read" />
						<?php esc_html_e( 'Read only — it can never change anything', 'erp' ); ?>
					</label>
				</p>
				<p class="submit" style="margin-top:20px;">
					<button type="submit" name="approve" value="1" class="button button-primary button-large"><?php esc_html_e( 'Allow', 'erp' ); ?></button>
					<button type="submit" name="deny" value="1" class="button button-large" style="float:right;margin-right:8px;"><?php esc_html_e( 'Cancel', 'erp' ); ?></button>
				</p>
			<?php else : ?>
				<p style="margin-top:12px;">
					<?php esc_html_e( 'Your account has not been given access to HR through AI apps. Ask your administrator to turn it on for you.', 'erp' ); ?>
				</p>
				<p class="submit" style="margin-top:20px;">
					<button type="submit" name="deny" value="1" class="button button-large"><?php esc_html_e( 'Go back to the app', 'erp' ); ?></button>
				</p>
			<?php endif; ?>
		</form>
		</div>
		</body>
		</html>
		<?php
	}
}
