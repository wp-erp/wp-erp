<?php
/**
 * WP-ERP HR — MCP access tokens.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Issues and verifies the access tokens AI clients authenticate with.
 *
 * A token stands in for one WordPress user, so everything a client can reach
 * through it is bounded by that user's own capabilities and by the access the
 * administrator has granted them. Tokens are stored as hashes: the plaintext
 * is shown once, at creation, and cannot be recovered afterwards.
 */
class TokenManager {

	/**
	 * Prefix on the visible part of a token, so a leaked string is
	 * recognisable in logs and can be searched for.
	 */
	const PREFIX = 'erp_hr_mcp_';

	/**
	 * Bytes of entropy behind each token.
	 */
	const SECRET_BYTES = 32;

	/**
	 * The scopes a token can carry.
	 *
	 * @var string[]
	 */
	const SCOPES = [ 'full', 'read' ];

	/**
	 * Prefix on a refresh token.
	 */
	const REFRESH_PREFIX = 'erp_hr_mcpr_';

	/**
	 * Seconds an access token issued through sign-in lasts before the app
	 * has to renew it. A token created by hand does not expire.
	 */
	const GRANT_TTL = HOUR_IN_SECONDS;

	/**
	 * The tokens table.
	 *
	 * @return string
	 */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'erp_hr_mcp_tokens';
	}

	/**
	 * Mint a token for a user.
	 *
	 * @param int    $user_id The user the token acts as.
	 * @param string $label   A name to recognise it by.
	 * @param string $scope   `full`, or `read` for a token that can never write.
	 *
	 * @return array{id: int, token: string}|WP_Error The plaintext token, returned
	 *                                                only here and never again.
	 */
	public function create( int $user_id, string $label, string $scope = 'full' ) {
		global $wpdb;

		if ( ! get_userdata( $user_id ) ) {
			return new WP_Error( 'erp_hr_mcp_invalid_user', __( 'That user does not exist.', 'erp' ), [ 'status' => 404 ] );
		}

		// A token cannot grant what its owner has not been given.
		if ( ! Access::can_connect( $user_id ) ) {
			return new WP_Error(
				'erp_hr_mcp_user_cannot_connect',
				__( 'That user has not been granted any access over MCP.', 'erp' ),
				[ 'status' => 403 ]
			);
		}

		$token    = self::PREFIX . bin2hex( random_bytes( self::SECRET_BYTES ) );
		$inserted = $wpdb->insert(
			self::table(),
			[
				'user_id'    => $user_id,
				'label'      => $label,
				'scope'      => in_array( $scope, self::SCOPES, true ) ? $scope : 'full',
				'token_hash' => self::hash( $token ),
				'created_at' => current_time( 'mysql', true ),
			],
			[ '%d', '%s', '%s', '%s', '%s' ]
		);

		if ( ! $inserted ) {
			return new WP_Error( 'erp_hr_mcp_token_failed', __( 'The token could not be created.', 'erp' ), [ 'status' => 500 ] );
		}

		return [
			'id'    => (int) $wpdb->insert_id,
			'token' => $token,
		];
	}

	/**
	 * Mint a token for an app the user signed in to through OAuth.
	 *
	 * Unlike a token created by hand this one expires, and comes with a
	 * refresh token the app renews it with.
	 *
	 * @param int    $user_id   The user the token acts as.
	 * @param string $label     The app's name.
	 * @param string $scope     `full` or `read`.
	 * @param string $client_id The OAuth client it was issued to.
	 *
	 * @return array{id: int, token: string, refresh_token: string, expires_in: int, scope: string}|WP_Error
	 */
	public function create_grant( int $user_id, string $label, string $scope, string $client_id ) {
		global $wpdb;

		$created = $this->create( $user_id, $label, $scope );

		if ( is_wp_error( $created ) ) {
			return $created;
		}

		$refresh = self::REFRESH_PREFIX . bin2hex( random_bytes( self::SECRET_BYTES ) );

		$wpdb->update(
			self::table(),
			[
				'client_id'    => $client_id,
				'refresh_hash' => self::hash( $refresh ),
				'expires_at'   => gmdate( 'Y-m-d H:i:s', time() + self::GRANT_TTL ),
			],
			[ 'id' => $created['id'] ],
			[ '%s', '%s', '%s' ],
			[ '%d' ]
		);

		return $created + [
			'refresh_token' => $refresh,
			'expires_in'    => self::GRANT_TTL,
			'scope'         => in_array( $scope, self::SCOPES, true ) ? $scope : 'full',
		];
	}

	/**
	 * Renew a signed-in app's token.
	 *
	 * Both the access token and the refresh token are replaced, on the same
	 * row, so the app stays one entry in the list and one thing to revoke.
	 *
	 * @param string $refresh_token The refresh token presented.
	 * @param string $client_id     The client presenting it.
	 *
	 * @return array{id: int, token: string, refresh_token: string, expires_in: int, scope: string}|null
	 */
	public function refresh( string $refresh_token, string $client_id ) {
		global $wpdb;

		if ( 0 !== strpos( $refresh_token, self::REFRESH_PREFIX ) ) {
			return null;
		}

		$table = self::table();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE refresh_hash = %s AND client_id = %s AND revoked_at IS NULL", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				self::hash( $refresh_token ),
				$client_id
			)
		);

		if ( ! $row || ! Access::can_connect( (int) $row->user_id ) ) {
			return null;
		}

		$token   = self::PREFIX . bin2hex( random_bytes( self::SECRET_BYTES ) );
		$refresh = self::REFRESH_PREFIX . bin2hex( random_bytes( self::SECRET_BYTES ) );

		// Conditional on the old refresh hash, so of two requests racing
		// with the same refresh token only one can win.
		$updated = $wpdb->update(
			$table,
			[
				'token_hash'   => self::hash( $token ),
				'refresh_hash' => self::hash( $refresh ),
				'expires_at'   => gmdate( 'Y-m-d H:i:s', time() + self::GRANT_TTL ),
			],
			[
				'id'           => (int) $row->id,
				'refresh_hash' => (string) $row->refresh_hash,
			],
			[ '%s', '%s', '%s' ],
			[ '%d', '%s' ]
		);

		if ( ! $updated ) {
			return null;
		}

		return [
			'id'            => (int) $row->id,
			'token'         => $token,
			'refresh_token' => $refresh,
			'expires_in'    => self::GRANT_TTL,
			'scope'         => (string) $row->scope,
		];
	}

	/**
	 * Resolve a presented token to its record.
	 *
	 * @param string $token The plaintext token from the request.
	 *
	 * @return object|null The token row, or null when it does not verify.
	 */
	public function verify( string $token ) {
		global $wpdb;

		if ( 0 !== strpos( $token, self::PREFIX ) ) {
			return null;
		}

		$table = self::table();

		// Looked up by hash rather than compared in PHP, so verification
		// cost does not grow with the number of tokens issued.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE token_hash = %s AND revoked_at IS NULL
				AND ( expires_at IS NULL OR expires_at > %s )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				self::hash( $token ),
				gmdate( 'Y-m-d H:i:s' )
			)
		);

		if ( ! $row ) {
			return null;
		}

		// A token is only as good as the account behind it: if the user was
		// deleted, or the administrator withdrew their access, the token
		// stops working at once without anyone having to revoke it.
		if ( ! Access::can_connect( (int) $row->user_id ) ) {
			return null;
		}

		return $row;
	}

	/**
	 * Note that a token was just used.
	 *
	 * @param int $token_id The token id.
	 *
	 * @return void
	 */
	public function touch( int $token_id ): void {
		global $wpdb;

		$wpdb->update(
			self::table(),
			[ 'last_used_at' => current_time( 'mysql', true ) ],
			[ 'id' => $token_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * List a user's tokens, or everyone's.
	 *
	 * Never returns anything from which a token could be reconstructed.
	 *
	 * @param int|null $user_id Restrict to one user, or null for all.
	 *
	 * @return array<int, object>
	 */
	public function all( ?int $user_id = null ): array {
		global $wpdb;

		$table = self::table();

		if ( null !== $user_id ) {
			return (array) $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, user_id, label, scope, created_at, last_used_at, revoked_at
					FROM {$table} WHERE user_id = %d ORDER BY created_at DESC, id DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$user_id
				)
			);
		}

		return (array) $wpdb->get_results(
			"SELECT id, user_id, label, scope, created_at, last_used_at, revoked_at
			FROM {$table} ORDER BY created_at DESC, id DESC" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Fetch a single token row.
	 *
	 * @param int $token_id The token id.
	 *
	 * @return object|null
	 */
	public function find( int $token_id ) {
		global $wpdb;

		$table = self::table();

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, user_id, label, scope, created_at, last_used_at, revoked_at FROM {$table} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$token_id
			)
		);
	}

	/**
	 * Revoke a token.
	 *
	 * Kept as a row rather than deleted, so the audit trail still resolves
	 * which token performed a past action.
	 *
	 * @param int $token_id The token id.
	 *
	 * @return bool
	 */
	public function revoke( int $token_id ): bool {
		global $wpdb;

		return (bool) $wpdb->update(
			self::table(),
			[ 'revoked_at' => current_time( 'mysql', true ) ],
			[ 'id' => $token_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Hash a token for storage and lookup.
	 *
	 * A keyed SHA-256: the token is already 256 bits of random, so there is
	 * nothing to brute-force and no need for the slow hashing a user-chosen
	 * password would want. Keyed on the site's salt so a stolen table is
	 * useless against another site.
	 *
	 * @param string $token The plaintext token.
	 *
	 * @return string
	 */
	private static function hash( string $token ): string {
		return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
	}
}
