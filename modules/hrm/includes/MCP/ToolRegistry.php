<?php
/**
 * WP-ERP HR — the MCP tool catalogue.
 */

namespace WeDevs\ERP\HRM\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * The catalogue of tools this server exposes.
 *
 * Holds every tool the plugin and its extensions ship, and answers the two
 * questions the server asks: what can this user see, and what runs for a
 * given name.
 */
class ToolRegistry {

	/**
	 * Instantiated tools, keyed by name.
	 *
	 * @var array<string, Tool>|null
	 */
	private $tools = null;

	/**
	 * Every tool that can run on this site, before any per-user filtering.
	 *
	 * @return array<string, Tool>
	 */
	public function all(): array {
		if ( null !== $this->tools ) {
			return $this->tools;
		}

		/**
		 * Filter the route-backed MCP tool definitions.
		 *
		 * The usual way to add a tool: describe an `erp/` REST route and the
		 * server does the rest. See RouteTool for the shape. WP ERP Pro adds
		 * its HR extensions' tools here.
		 *
		 * @param array<int, array<string, mixed>> $definitions Tool definitions.
		 */
		$definitions = (array) apply_filters( 'erp_hr_mcp_tool_definitions', Catalog::definitions() );

		/**
		 * Filter the MCP tools the server exposes.
		 *
		 * For tools that need code of their own rather than a route. Each
		 * must extend the Tool base class.
		 *
		 * @param Tool[] $tools The registered tools.
		 */
		$tools = (array) apply_filters( 'erp_hr_mcp_tools', RouteTool::from_definitions( $definitions ) );

		$groups      = Access::groups();
		$this->tools = [];

		foreach ( $tools as $tool ) {
			// The filters are open to any plugin, so what comes back is only
			// a Tool by convention. Anything else is dropped rather than
			// allowed to fatal later, when it is called.
			if ( ! $tool instanceof Tool ) {
				continue;
			}

			// A tool outside every known group could not be granted to
			// anyone, and one whose route is gone could not run.
			if ( ! isset( $groups[ $tool->get_group() ] ) || ! $tool->is_available() ) {
				continue;
			}

			$this->tools[ $tool->get_name() ] = $tool;
		}

		return $this->tools;
	}

	/**
	 * The tools a given user may both see and run.
	 *
	 * Filtering happens at listing time as well as call time, so a model is
	 * never shown a tool it would only be refused for using.
	 *
	 * @param int    $user_id The user the token belongs to.
	 * @param string $scope   The token's own ceiling: `full`, or `read` for a
	 *                        read-only token.
	 *
	 * @return array<string, Tool>
	 */
	public function available_for( int $user_id, string $scope = 'full' ): array {
		$disabled  = Settings::get()['disabled_tools'];
		$levels    = Access::levels_for( $user_id );
		$available = [];

		foreach ( $this->all() as $name => $tool ) {
			if ( in_array( $name, $disabled, true ) ) {
				continue;
			}

			if ( 'read' === $scope && Tool::MODE_READ !== $tool->get_mode() ) {
				continue;
			}

			if ( ! Access::allows( $levels[ $tool->get_group() ] ?? Access::LEVEL_NONE, $tool->get_mode() ) ) {
				continue;
			}

			$capability = $tool->get_capability();

			if ( $capability && ! user_can( $user_id, $capability ) ) {
				continue;
			}

			$available[ $name ] = $tool;
		}

		return $available;
	}

	/**
	 * Why a user cannot use a tool that exists, for the audit log.
	 *
	 * @param Tool   $tool    The tool.
	 * @param int    $user_id The calling user.
	 * @param string $scope   The token's scope.
	 *
	 * @return string
	 */
	public function refusal_reason( Tool $tool, int $user_id, string $scope = 'full' ): string {
		if ( in_array( $tool->get_name(), Settings::get()['disabled_tools'], true ) ) {
			return __( 'This tool is switched off for this site.', 'erp' );
		}

		if ( 'read' === $scope && Tool::MODE_READ !== $tool->get_mode() ) {
			return __( 'This access token is read-only.', 'erp' );
		}

		if ( ! Access::allows( Access::level_for( $user_id, $tool->get_group() ), $tool->get_mode() ) ) {
			return __( 'The administrator has not granted this user access to this feature over MCP.', 'erp' );
		}

		return __( 'This user does not have the capability this tool needs.', 'erp' );
	}

	/**
	 * Look up a single tool by name.
	 *
	 * @param string $name The tool name.
	 *
	 * @return Tool|null
	 */
	public function get( string $name ): ?Tool {
		$tools = $this->all();

		return $tools[ $name ] ?? null;
	}
}
