<?php
/**
 * WP-ERP HR — who may reach which MCP feature.
 */

namespace WeDevs\ERP\HRM\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * The administrator's access layer.
 *
 * Sits on top of the REST permission checks, never instead of them. A tool
 * runs through the same `erp/v2` route the HR screens use, as the user the
 * token belongs to, so WordPress capabilities are still enforced on every
 * call. This class answers a narrower, separate question the administrator
 * controls: is this person allowed to use this feature *through an AI client*
 * at all, and how far — read it, change it, or delete from it.
 *
 * Access is granted per feature group, first by role and then, where an
 * individual needs something different, per user. A per-user rule replaces
 * the role rules for that person entirely.
 */
class Access {

	const LEVEL_NONE  = 'none';
	const LEVEL_READ  = 'read';
	const LEVEL_WRITE = 'write';
	const LEVEL_FULL  = 'full';

	/**
	 * Levels in ascending order of reach.
	 *
	 * @var string[]
	 */
	const LEVELS = [ self::LEVEL_NONE, self::LEVEL_READ, self::LEVEL_WRITE, self::LEVEL_FULL ];

	/**
	 * The level a tool of each mode needs.
	 *
	 * @var array<string, string>
	 */
	const MODE_LEVEL = [
		Tool::MODE_READ   => self::LEVEL_READ,
		Tool::MODE_WRITE  => self::LEVEL_WRITE,
		Tool::MODE_DELETE => self::LEVEL_FULL,
	];

	/**
	 * The feature groups tools are sorted into.
	 *
	 * @return array<string, array{label: string, description: string, pro: bool}>
	 */
	public static function groups(): array {
		$groups = [
			'self'          => [
				'label'       => __( 'Self service', 'erp' ),
				'description' => __( 'A person\'s own profile, leave balance, leave requests and announcements. Never reaches anyone else\'s records.', 'erp' ),
				'pro'         => false,
			],
			'employees'     => [
				'label'       => __( 'Employees', 'erp' ),
				'description' => __( 'Employee directory, profiles, job history, notes, performance and termination.', 'erp' ),
				'pro'         => false,
			],
			'organization'  => [
				'label'       => __( 'Organization', 'erp' ),
				'description' => __( 'Departments, designations, company locations and the org chart.', 'erp' ),
				'pro'         => false,
			],
			'leave'         => [
				'label'       => __( 'Leave', 'erp' ),
				'description' => __( 'Leave requests and approvals, leave types, policies, entitlements, holidays and the leave calendar.', 'erp' ),
				'pro'         => false,
			],
			'announcements' => [
				'label'       => __( 'Announcements', 'erp' ),
				'description' => __( 'Company announcements and who they are sent to.', 'erp' ),
				'pro'         => false,
			],
			'reports'       => [
				'label'       => __( 'Reports', 'erp' ),
				'description' => __( 'HR dashboard and reports: headcount, age and gender profile, salary history, years of service, leave.', 'erp' ),
				'pro'         => false,
			],
		];

		/**
		 * Filter the MCP feature groups.
		 *
		 * WP ERP Pro adds a group per HR extension here.
		 *
		 * @param array<string, array{label: string, description: string, pro: bool}> $groups Groups keyed by slug.
		 */
		$groups = (array) apply_filters( 'erp_hr_mcp_groups', $groups );

		foreach ( $groups as $slug => $group ) {
			$groups[ $slug ] = [
				'label'       => isset( $group['label'] ) ? (string) $group['label'] : (string) $slug,
				'description' => isset( $group['description'] ) ? (string) $group['description'] : '',
				'pro'         => ! empty( $group['pro'] ),
			];
		}

		return $groups;
	}

	/**
	 * The roles access can be granted to.
	 *
	 * @return array<string, string> Role label keyed by role slug.
	 */
	public static function roles(): array {
		$roles = [ 'administrator' => __( 'Administrator', 'erp' ) ];

		foreach ( erp_hr_get_roles() as $slug => $role ) {
			$roles[ $slug ] = $role['name'];
		}

		/**
		 * Filter the roles MCP access can be granted to.
		 *
		 * @param array<string, string> $roles Role label keyed by role slug.
		 */
		return (array) apply_filters( 'erp_hr_mcp_roles', $roles );
	}

	/**
	 * The level a role holds when the administrator has not said otherwise.
	 *
	 * Administrators get everything and HR managers everything short of
	 * deleting, which is what they already have in the HR screens. Everyone
	 * else — employees included — starts with nothing, so an administrator
	 * who switches the server on has not also opened it to the whole company
	 * without meaning to.
	 *
	 * @param string $role The role slug.
	 *
	 * @return string
	 */
	public static function default_level( string $role ): string {
		if ( 'administrator' === $role ) {
			return self::LEVEL_FULL;
		}

		if ( erp_hr_get_manager_role() === $role ) {
			return self::LEVEL_WRITE;
		}

		return self::LEVEL_NONE;
	}

	/**
	 * Clean an access map as stored in, or submitted for, the settings.
	 *
	 * Unknown levels are dropped rather than guessed at. Unknown group slugs
	 * are kept: a Pro extension that is switched off for a while should find
	 * its rules as they were when it comes back.
	 *
	 * @param mixed $access The raw access map.
	 *
	 * @return array{roles: array<string, array<string, string>>, users: array<int, array{mode: string, groups: array<string, string>}>}
	 */
	public static function sanitize( $access ): array {
		$clean = [
			'roles' => [],
			'users' => [],
		];

		if ( ! is_array( $access ) ) {
			return $clean;
		}

		foreach ( (array) ( $access['roles'] ?? [] ) as $role => $groups ) {
			$clean['roles'][ sanitize_key( $role ) ] = self::sanitize_groups( $groups );
		}

		foreach ( (array) ( $access['users'] ?? [] ) as $user_id => $rule ) {
			$user_id = (int) $user_id;

			if ( $user_id < 1 || ! is_array( $rule ) ) {
				continue;
			}

			$clean['users'][ $user_id ] = [
				'mode'   => isset( $rule['mode'] ) && 'blocked' === $rule['mode'] ? 'blocked' : 'custom',
				'groups' => self::sanitize_groups( $rule['groups'] ?? [] ),
			];
		}

		return $clean;
	}

	/**
	 * Clean one group => level map.
	 *
	 * @param mixed $groups The raw map.
	 *
	 * @return array<string, string>
	 */
	private static function sanitize_groups( $groups ): array {
		$clean = [];

		foreach ( (array) $groups as $group => $level ) {
			if ( in_array( $level, self::LEVELS, true ) ) {
				$clean[ sanitize_key( $group ) ] = $level;
			}
		}

		return $clean;
	}

	/**
	 * The level a user holds on one feature group.
	 *
	 * @param int    $user_id The user.
	 * @param string $group   The group slug.
	 *
	 * @return string One of the LEVEL_* constants.
	 */
	public static function level_for( int $user_id, string $group ): string {
		$levels = self::levels_for( $user_id );

		return $levels[ $group ] ?? self::LEVEL_NONE;
	}

	/**
	 * The level a user holds on every feature group.
	 *
	 * @param int $user_id The user.
	 *
	 * @return array<string, string> Level keyed by group slug.
	 */
	public static function levels_for( int $user_id ): array {
		$groups = array_keys( self::groups() );
		$levels = array_fill_keys( $groups, self::LEVEL_NONE );
		$user   = get_userdata( $user_id );

		if ( ! $user ) {
			return $levels;
		}

		$access = Settings::get()['access'];

		// An individual rule replaces the role rules outright. Merging the
		// two would leave an administrator unable to give one person less
		// than their role gets.
		if ( isset( $access['users'][ $user_id ] ) ) {
			$rule = $access['users'][ $user_id ];

			if ( 'blocked' === $rule['mode'] ) {
				return $levels;
			}

			foreach ( $groups as $group ) {
				$levels[ $group ] = $rule['groups'][ $group ] ?? self::LEVEL_NONE;
			}

			return $levels;
		}

		$known = self::roles();

		foreach ( (array) $user->roles as $role ) {
			if ( ! isset( $known[ $role ] ) ) {
				continue;
			}

			foreach ( $groups as $group ) {
				$level = $access['roles'][ $role ][ $group ] ?? self::default_level( $role );

				// A person with two roles gets the wider of the two.
				if ( self::rank( $level ) > self::rank( $levels[ $group ] ) ) {
					$levels[ $group ] = $level;
				}
			}
		}

		return $levels;
	}

	/**
	 * Whether a user may connect an AI client at all.
	 *
	 * @param int $user_id The user.
	 *
	 * @return bool
	 */
	public static function can_connect( int $user_id ): bool {
		foreach ( self::levels_for( $user_id ) as $level ) {
			if ( self::LEVEL_NONE !== $level ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether a level is enough for a tool of the given mode.
	 *
	 * @param string $level The level held.
	 * @param string $mode  The tool's mode.
	 *
	 * @return bool
	 */
	public static function allows( string $level, string $mode ): bool {
		$needed = self::MODE_LEVEL[ $mode ] ?? self::LEVEL_FULL;

		return self::rank( $level ) >= self::rank( $needed );
	}

	/**
	 * A level's position in the ordering.
	 *
	 * @param string $level The level.
	 *
	 * @return int
	 */
	public static function rank( string $level ): int {
		$rank = array_search( $level, self::LEVELS, true );

		return false === $rank ? 0 : (int) $rank;
	}

	/**
	 * Whether a user administers the MCP server itself.
	 *
	 * @param int $user_id The user; the current one when omitted.
	 *
	 * @return bool
	 */
	public static function is_admin( int $user_id = 0 ): bool {
		return $user_id ? user_can( $user_id, 'manage_options' ) : current_user_can( 'manage_options' );
	}
}
