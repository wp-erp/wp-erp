<?php
/**
 * WP-ERP HR — the MCP Streamable HTTP transport.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * The Streamable HTTP transport, as a REST route.
 *
 * MCP defines two transports. stdio wants to be launched as a subprocess,
 * which a web server cannot offer, so this implements Streamable HTTP: one
 * endpoint taking POST for messages.
 *
 * The server is deliberately stateless — it issues no session id and always
 * answers with a single JSON object rather than an SSE stream. Nothing here
 * needs to push to the client, and PHP has no process to hold a session in
 * between requests. Both are explicitly permitted by the spec.
 */
class Transport {

	/**
	 * The protocol handler.
	 *
	 * @var Server
	 */
	private $server;

	/**
	 * The token verifier.
	 *
	 * @var TokenManager
	 */
	private $tokens;

	/**
	 * Constructor.
	 *
	 * @param Server       $server The protocol handler.
	 * @param TokenManager $tokens The token verifier.
	 */
	public function __construct( Server $server, TokenManager $tokens ) {
		$this->server = $server;
		$this->tokens = $tokens;
	}

	/**
	 * Register the endpoint.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Settings::REST_NAMESPACE,
			Settings::REST_ROUTE,
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'handle_post' ],
					// Authentication is a bearer token rather than a
					// logged-in cookie, so it is resolved inside the handler.
					// The route guard only confirms the endpoint is on.
					'permission_callback' => [ $this, 'check_enabled' ],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'handle_get' ],
					'permission_callback' => [ $this, 'check_enabled' ],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'handle_delete' ],
					'permission_callback' => [ $this, 'check_enabled' ],
				],
			]
		);
	}

	/**
	 * Refuse everything unless an administrator has switched the server on.
	 *
	 * @return bool|WP_Error
	 */
	public function check_enabled() {
		if ( ! Settings::get()['enabled'] ) {
			return new WP_Error(
				'erp_hr_mcp_disabled',
				__( 'The WP ERP HR MCP server is not enabled on this site.', 'erp' ),
				[ 'status' => 404 ]
			);
		}

		return true;
	}

	/**
	 * Handle a JSON-RPC message.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_post( WP_REST_Request $request ): WP_REST_Response {
		$origin = $this->origin_check( $request );

		if ( $origin instanceof WP_REST_Response ) {
			return $origin;
		}

		$message = json_decode( $request->get_body(), true );

		if ( ! is_array( $message ) ) {
			return $this->failure( -32700, __( 'Could not parse the request body as JSON.', 'erp' ), 400 );
		}

		$version = $this->version_check( $request, $message );

		if ( $version instanceof WP_REST_Response ) {
			return $version;
		}

		$guard = $this->authenticate( $request );

		// Refused with an HTTP 401, the handshake included: that status is
		// what tells an app to start signing in. A JSON-RPC error would be
		// read as the call failing rather than as missing credentials.
		if ( $guard instanceof WP_REST_Response ) {
			return $guard;
		}

		return $this->dispatch( $message, $guard[0], $guard[1], $guard[2] );
	}

	/**
	 * Answer a GET, which would open a server-initiated stream.
	 *
	 * This server never initiates anything, so the spec's prescribed reply is
	 * 405, telling the client not to wait on a stream that will not arrive.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_get(): WP_REST_Response {
		return $this->failure( -32601, __( 'This server does not offer a server-initiated event stream.', 'erp' ), 405 );
	}

	/**
	 * Answer a DELETE, which would end a session this server never opened.
	 *
	 * @return WP_REST_Response
	 */
	public function handle_delete(): WP_REST_Response {
		return $this->failure( -32601, __( 'This server is stateless and has no session to end.', 'erp' ), 405 );
	}

	/**
	 * Run a message, or each message in a batch, and wrap the reply.
	 *
	 * @param array<mixed> $message  A single decoded message, or a list of them.
	 * @param int          $user_id  The calling user.
	 * @param int          $token_id The token used.
	 * @param string       $scope    The token's scope.
	 *
	 * @return WP_REST_Response
	 */
	private function dispatch( array $message, int $user_id, int $token_id, string $scope ): WP_REST_Response {
		if ( ! $this->is_list( $message ) ) {
			$reply = $this->server->handle( $message, $user_id, $token_id, $scope );

			// Notifications get an acknowledgement with no body.
			return null === $reply ? new WP_REST_Response( null, 202 ) : new WP_REST_Response( $reply, 200 );
		}

		$replies = [];

		foreach ( $message as $item ) {
			// A batch may contain anything a client chose to send, so an
			// entry that is not a message is skipped rather than trusted.
			if ( ! is_array( $item ) ) {
				continue;
			}

			$reply = $this->server->handle( $item, $user_id, $token_id, $scope );

			if ( null !== $reply ) {
				$replies[] = $reply;
			}
		}

		// A batch of nothing but notifications warrants an acknowledgement
		// rather than an empty array, which is not a valid JSON-RPC reply.
		return empty( $replies ) ? new WP_REST_Response( null, 202 ) : new WP_REST_Response( $replies, 200 );
	}

	/**
	 * Reject a request whose Origin we do not trust.
	 *
	 * A browser will attach ambient credentials to a cross-site request, so
	 * an endpoint that skips this can be driven by any page the victim
	 * visits. Requests with no Origin at all come from a non-browser client,
	 * which is the normal case for MCP and carries no ambient authority.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return true|WP_REST_Response
	 */
	private function origin_check( WP_REST_Request $request ) {
		$origin = $request->get_header( 'origin' );

		if ( ! $origin ) {
			return true;
		}

		/**
		 * Filter the origins allowed to reach the MCP endpoint.
		 *
		 * @param string[] $origins The configured origins.
		 */
		$allowed = (array) apply_filters( 'erp_hr_mcp_allowed_origins', Settings::get()['allowed_origins'] );

		// The site's own origin is always acceptable.
		$allowed[] = $this->origin_of( site_url() );
		$allowed[] = $this->origin_of( home_url() );

		foreach ( $allowed as $candidate ) {
			if ( 0 === strcasecmp( untrailingslashit( $origin ), untrailingslashit( (string) $candidate ) ) ) {
				return true;
			}
		}

		return $this->failure( -32600, __( 'This origin is not allowed to reach the MCP endpoint.', 'erp' ), 403 );
	}

	/**
	 * The scheme and host of a URL, which is all an Origin header carries.
	 *
	 * @param string $url The URL.
	 *
	 * @return string
	 */
	private function origin_of( string $url ): string {
		$parts = wp_parse_url( $url );

		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return $url;
		}

		return $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
	}

	/**
	 * Reject a protocol version we cannot speak.
	 *
	 * The spec says to assume 2025-03-26 when the header is missing, so only
	 * a present-but-unknown value is an error. The handshake is exempt: the
	 * header carries the version agreed during initialization, so a client
	 * that sends its own latest on the initialize call is not in the wrong,
	 * and Server::initialize() answers it with a revision we do speak.
	 *
	 * @param WP_REST_Request $request The request.
	 * @param array<mixed>    $message The decoded body.
	 *
	 * @return true|WP_REST_Response
	 */
	private function version_check( WP_REST_Request $request, array $message ) {
		$version = $request->get_header( 'mcp_protocol_version' );

		if ( ! $version || in_array( $version, Server::SUPPORTED_VERSIONS, true ) || $this->is_handshake( $message ) ) {
			return true;
		}

		return $this->failure(
			-32600,
			sprintf(
				/* translators: 1: the requested version, 2: comma-separated supported versions. */
				__( 'Unsupported MCP protocol version %1$s. This server supports: %2$s', 'erp' ),
				$version,
				implode( ', ', Server::SUPPORTED_VERSIONS )
			),
			400
		);
	}

	/**
	 * Whether a decoded message is a batch: a plain list rather than one keyed object.
	 *
	 * @param array<mixed> $value The decoded message.
	 *
	 * @return bool
	 */
	private function is_list( array $value ): bool {
		return [] !== $value && array_keys( $value ) === range( 0, count( $value ) - 1 );
	}

	/**
	 * Whether a message is the handshake, and so exempt from the version header.
	 *
	 * A batch qualifies only if every message in it is the handshake, so a
	 * client cannot smuggle an ordinary call past the check by pairing it
	 * with an initialize.
	 *
	 * @param array<mixed> $message A single decoded message, or a batch.
	 *
	 * @return bool
	 */
	private function is_handshake( array $message ): bool {
		foreach ( $this->is_list( $message ) ? $message : [ $message ] as $item ) {
			if ( ! is_array( $item ) || 'initialize' !== ( $item['method'] ?? '' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Resolve the bearer token to a user.
	 *
	 * The tools dispatch REST routes that call current_user_can() throughout,
	 * so the resolved user is set as the current user for the rest of the
	 * request. Without that, every capability check would run against nobody.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return array{0: int, 1: int, 2: string}|WP_REST_Response User id, token id and token scope.
	 */
	private function authenticate( WP_REST_Request $request ) {
		$header = (string) $request->get_header( 'authorization' );

		if ( ! preg_match( '/^Bearer\s+(\S+)$/i', trim( $header ), $matches ) ) {
			return $this->unauthorized( __( 'A bearer token is required.', 'erp' ) );
		}

		$token = $this->tokens->verify( $matches[1] );

		if ( ! $token ) {
			return $this->unauthorized( __( 'That access token is not valid.', 'erp' ) );
		}

		wp_set_current_user( (int) $token->user_id );

		$this->tokens->touch( (int) $token->id );

		return [ (int) $token->user_id, (int) $token->id, (string) $token->scope ];
	}

	/**
	 * Build a 401 with the challenge header clients expect.
	 *
	 * @param string $message What was wrong.
	 *
	 * @return WP_REST_Response
	 */
	private function unauthorized( string $message ): WP_REST_Response {
		$response = $this->failure( -32600, $message, 401 );

		// Points an app that can sign in at where to find out how.
		$response->header(
			'WWW-Authenticate',
			sprintf( 'Bearer realm="WP ERP HR MCP", error="invalid_token", resource_metadata="%s"', OAuth::resource_metadata_url() )
		);

		return $response;
	}

	/**
	 * A JSON-RPC error raised by the transport itself.
	 *
	 * @param int    $code    The JSON-RPC error code.
	 * @param string $message The error message.
	 * @param int    $status  The HTTP status.
	 *
	 * @return WP_REST_Response
	 */
	private function failure( int $code, string $message, int $status ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'jsonrpc' => '2.0',
				'id'      => null,
				'error'   => [
					'code'    => $code,
					'message' => $message,
				],
			],
			$status
		);
	}
}
