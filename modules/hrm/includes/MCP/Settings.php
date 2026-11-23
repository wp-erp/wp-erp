<?php
/**
 * WP-ERP HR — MCP server settings.
 */

namespace WeDevs\ERP\HRM\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * The MCP server's own settings.
 *
 * Kept in a dedicated option rather than the shared ERP settings, because
 * these govern an externally reachable endpoint and are read on every MCP
 * request.
 */
class Settings {

	/**
	 * The option name.
	 */
	const OPTION = 'erp_hr_mcp_settings';

	/**
	 * The REST namespace the endpoint lives in.
	 */
	const REST_NAMESPACE = 'erp/v2';

	/**
	 * The endpoint path inside the namespace.
	 */
	const REST_ROUTE = '/hrm/mcp';

	/**
	 * Read the settings, with defaults filled in.
	 *
	 * @return array{enabled: bool, disabled_tools: string[], allowed_origins: string[], log_retention_days: int, access: array}
	 */
	public static function get(): array {
		$stored = get_option( self::OPTION, [] );

		if ( ! is_array( $stored ) ) {
			$stored = [];
		}

		$settings = array_merge(
			[
				// Off until an administrator turns it on: a fresh install
				// must not expose an endpoint nobody asked for.
				'enabled'            => false,
				'disabled_tools'     => [],
				'allowed_origins'    => [],
				'log_retention_days' => 30,
				'access'             => [],
			],
			$stored
		);

		return [
			'enabled'            => (bool) $settings['enabled'],
			'disabled_tools'     => array_values( array_filter( array_map( 'strval', (array) $settings['disabled_tools'] ) ) ),
			'allowed_origins'    => array_values( array_filter( array_map( 'strval', (array) $settings['allowed_origins'] ) ) ),
			'log_retention_days' => max( 0, (int) $settings['log_retention_days'] ),
			'access'             => Access::sanitize( $settings['access'] ),
		];
	}

	/**
	 * Save settings, merging over what is already stored.
	 *
	 * @param array<string, mixed> $changes The values to change.
	 *
	 * @return array<string, mixed> The settings as they now stand.
	 */
	public static function update( array $changes ): array {
		$merged = array_merge( self::get(), array_intersect_key( $changes, self::get() ) );

		$clean = [
			'enabled'            => (bool) $merged['enabled'],
			'disabled_tools'     => array_values(
				array_unique( array_filter( array_map( 'sanitize_key', (array) $merged['disabled_tools'] ) ) )
			),
			'allowed_origins'    => array_values(
				array_unique( array_filter( array_map( 'esc_url_raw', (array) $merged['allowed_origins'] ) ) )
			),
			'log_retention_days' => max( 0, (int) $merged['log_retention_days'] ),
			'access'             => Access::sanitize( $merged['access'] ),
		];

		update_option( self::OPTION, $clean );

		return $clean;
	}

	/**
	 * The URL clients point at.
	 *
	 * @return string
	 */
	public static function endpoint_url(): string {
		return rest_url( self::REST_NAMESPACE . self::REST_ROUTE );
	}
}
