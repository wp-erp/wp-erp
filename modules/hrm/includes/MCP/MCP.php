<?php
/**
 * WP-ERP HR — the MCP subsystem.
 */

namespace WeDevs\ERP\HRM\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * The MCP (Model Context Protocol) subsystem.
 *
 * Lets an AI client — Claude, Cursor and the like — work with HR data as a
 * particular WordPress user. Wires the endpoint and its admin API into REST,
 * keeps its tables present, and prunes the audit log.
 */
class MCP {

	/**
	 * Bumped whenever this subsystem's tables change shape.
	 */
	const SCHEMA_VERSION = 2;

	/**
	 * Where the installed schema version is recorded.
	 */
	const SCHEMA_OPTION = 'erp_hr_mcp_schema_version';

	/**
	 * The nightly log-pruning event.
	 */
	const PRUNE_HOOK = 'erp_hr_mcp_prune_log';

	/**
	 * Whether init() has run.
	 *
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Boot the subsystem. Idempotent.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		$instance = new self();

		add_action( 'rest_api_init', [ $instance, 'register_routes' ] );
		add_action( 'admin_init', [ $instance, 'maybe_install' ] );
		add_action( 'admin_init', [ $instance, 'schedule_pruning' ] );
		add_action( self::PRUNE_HOOK, [ $instance, 'prune_log' ] );

		// The well-known documents and the consent screen sit outside REST.
		( new OAuth( new TokenManager() ) )->register();

		// Other plugins that accept bearer tokens must not get to refuse ours.
		( new AuthShield() )->register();
	}

	/**
	 * Register the MCP endpoint and the admin API behind the settings screen.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$registry = new ToolRegistry();
		$tokens   = new TokenManager();
		$audit    = new AuditLog();

		( new Transport( new Server( $registry, $audit ), $tokens ) )->register_routes();
		( new AdminController( $registry, $tokens, $audit ) )->register_routes();
		( new OAuth( $tokens ) )->register_routes();
	}

	/**
	 * Create or update this subsystem's tables when needed.
	 *
	 * The plugin creates its tables on activation only, so a site that
	 * updates in place never runs the installer again. This subsystem checks
	 * its own schema instead of relying on a reactivation.
	 *
	 * @return void
	 */
	public function maybe_install(): void {
		if ( self::SCHEMA_VERSION === (int) get_option( self::SCHEMA_OPTION ) ) {
			return;
		}

		self::install();
	}

	/**
	 * Create the tables.
	 *
	 * @return void
	 */
	public static function install(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$tokens          = TokenManager::table();
		$log             = AuditLog::table();
		$clients         = OAuth::table();

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta(
			"CREATE TABLE {$tokens} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			label varchar(191) NOT NULL,
			scope varchar(20) NOT NULL DEFAULT 'full',
			token_hash char(64) NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_used_at datetime DEFAULT NULL,
			revoked_at datetime DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			client_id varchar(64) DEFAULT NULL,
			refresh_hash char(64) DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY uk_token_hash (token_hash),
			KEY idx_user_id (user_id),
			KEY idx_refresh_hash (refresh_hash)
		) {$charset_collate};"
		);

		dbDelta(
			"CREATE TABLE {$clients} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			client_id varchar(64) NOT NULL,
			name varchar(191) NOT NULL,
			redirect_uris longtext NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_used_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY uk_client_id (client_id)
		) {$charset_collate};"
		);

		dbDelta(
			"CREATE TABLE {$log} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			tool varchar(100) NOT NULL,
			arguments longtext DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'success',
			message text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY idx_user_id (user_id),
			KEY idx_created_at (created_at)
		) {$charset_collate};"
		);

		// Recorded only once the tables are really there. A dbDelta that
		// failed must leave maybe_install() a reason to try again.
		$found = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'erp_hr_mcp_' ) . '%' ) );

		if ( in_array( $tokens, (array) $found, true ) && in_array( $log, (array) $found, true ) && in_array( $clients, (array) $found, true ) ) {
			update_option( self::SCHEMA_OPTION, self::SCHEMA_VERSION );
		}
	}

	/**
	 * Make sure the pruning event is scheduled.
	 *
	 * @return void
	 */
	public function schedule_pruning(): void {
		if ( ! wp_next_scheduled( self::PRUNE_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::PRUNE_HOOK );
		}
	}

	/**
	 * Drop log entries past the retention window.
	 *
	 * @return void
	 */
	public function prune_log(): void {
		OAuth::prune_clients();

		$days = Settings::get()['log_retention_days'];

		// Zero means keep everything, which some sites will want for their
		// own compliance reasons.
		if ( $days > 0 ) {
			( new AuditLog() )->prune( $days );
		}
	}
}
