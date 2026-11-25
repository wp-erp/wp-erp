<?php
/**
 * WP-ERP HR — the MCP audit log.
 */

namespace WeDevs\ERP\HRM\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * A record of every tool call made over MCP.
 *
 * An AI client acts with a person's authority but without that person
 * watching each step, so the administrator needs somewhere to see what was
 * actually done, by whose token, and what was refused.
 */
class AuditLog {

	/**
	 * The log table.
	 *
	 * @return string
	 */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'erp_hr_mcp_log';
	}

	/**
	 * Record one call.
	 *
	 * @param int                  $token_id  The token used.
	 * @param int                  $user_id   The user it acted as.
	 * @param string               $tool      The tool name.
	 * @param array<string, mixed> $arguments The arguments it was given.
	 * @param string               $status    "success", "error" or "refused".
	 * @param string|null          $message   The failure reason, when it failed.
	 *
	 * @return void
	 */
	public function record( int $token_id, int $user_id, string $tool, array $arguments, string $status, ?string $message = null ): void {
		global $wpdb;

		$wpdb->insert(
			self::table(),
			[
				'token_id'   => $token_id,
				'user_id'    => $user_id,
				'tool'       => $tool,
				'arguments'  => (string) wp_json_encode( $this->redact( $arguments ) ),
				'status'     => $status,
				'message'    => $message,
				'created_at' => current_time( 'mysql', true ),
			],
			[ '%d', '%d', '%s', '%s', '%s', '%s', '%s' ]
		);
	}

	/**
	 * Read the log, newest first.
	 *
	 * @param int      $limit   How many rows.
	 * @param int      $offset  Where to start.
	 * @param int|null $user_id Restrict to one user, or null for everyone.
	 *
	 * @return array<int, object>
	 */
	public function recent( int $limit = 50, int $offset = 0, ?int $user_id = null ): array {
		global $wpdb;

		$table = self::table();

		if ( null !== $user_id ) {
			return (array) $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$user_id,
					$limit,
					$offset
				)
			);
		}

		return (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$limit,
				$offset
			)
		);
	}

	/**
	 * Total rows in the log.
	 *
	 * @param int|null $user_id Restrict to one user, or null for everyone.
	 *
	 * @return int
	 */
	public function count( ?int $user_id = null ): int {
		global $wpdb;

		$table = self::table();

		if ( null !== $user_id ) {
			return (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Drop entries older than the retention window.
	 *
	 * @param int $days How long to keep.
	 *
	 * @return int Rows removed.
	 */
	public function prune( int $days ): int {
		global $wpdb;

		if ( $days < 1 ) {
			return 0;
		}

		$table = self::table();

		return (int) $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);
	}

	/**
	 * Keep long values out of the log.
	 *
	 * Arguments are stored so an administrator can see what a call did, but
	 * the log should stay readable and should not become a second copy of
	 * every note and announcement body written through it.
	 *
	 * @param array<string, mixed> $arguments The raw arguments.
	 *
	 * @return array<string, mixed>
	 */
	private function redact( array $arguments ): array {
		foreach ( $arguments as $key => $value ) {
			if ( is_string( $value ) && strlen( $value ) > 300 ) {
				$arguments[ $key ] = substr( $value, 0, 300 ) . '…';
			}
		}

		return $arguments;
	}
}
