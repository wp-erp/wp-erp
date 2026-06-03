<?php
/**
 * WP-ERP HR — `erp/v2` REST controller manager.
 *
 * Dokan-style auto-loader for the v2 namespace. Hooks once on `rest_api_init`
 * and instantiates each registered controller. Pro plugins add their own v2
 * controllers via the `erp_hrm_v2_rest_api_controllers` filter — they never
 * touch this file.
 *
 * Independent of the v1 registration pipeline (`erp_rest_api_controllers`
 * filter into `WeDevs\ERP\API\ApiRegistrar`); v1 stays frozen.
 */

namespace WeDevs\ERP\HRM\API\V2;

defined( 'ABSPATH' ) || exit;

class Manager {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * True once the rest_api_init hook has been added.
	 *
	 * @var bool
	 */
	private $booted = false;

	private function __construct() {}

	/**
	 * Boot the manager. Idempotent.
	 *
	 * @return self
	 */
	public static function init(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		if ( ! self::$instance->booted ) {
			add_action( 'rest_api_init', [ self::$instance, 'register_controllers' ], 10 );
			self::$instance->booted = true;
		}

		return self::$instance;
	}

	/**
	 * Instantiate every registered v2 controller and call `register_routes()`.
	 *
	 * Pro plugins add to the controller list via the
	 * `erp_hrm_v2_rest_api_controllers` filter.
	 *
	 * @return void
	 */
	public function register_controllers(): void {
		if ( ! class_exists( '\WP_REST_Server' ) ) {
			return;
		}

		$controllers = [
			MeControllerV2::class,
			EmployeesControllerV2::class,
			EmployeeUserControllerV2::class,
			EmployeeNotesControllerV2::class,
			EmployeeProfileControllerV2::class,
			EmployeeJobHistoriesControllerV2::class,
			EmployeeLeaveControllerV2::class,
			EmployeePerformanceControllerV2::class,
			EmployeePermissionControllerV2::class,
			DepartmentsControllerV2::class,
			DesignationsControllerV2::class,
			HolidaysControllerV2::class,
			LeaveTypesControllerV2::class,
			LeavePoliciesControllerV2::class,
			LeaveEntitlementsControllerV2::class,
			LeaveRequestsControllerV2::class,
		];

		/**
		 * Filter the list of `erp/v2` REST controllers.
		 *
		 * Pro plugins add fully-qualified class names that extend
		 * `WeDevs\ERP\HRM\API\V2\RestControllerV2` and implement
		 * `register_routes()`.
		 *
		 * @since 1.13.5
		 *
		 * @param string[] $controllers Fully-qualified class names.
		 */
		$controllers = (array) apply_filters( 'erp_hrm_v2_rest_api_controllers', $controllers );

		foreach ( $controllers as $controller_fqcn ) {
			if ( ! is_string( $controller_fqcn ) || ! class_exists( $controller_fqcn ) ) {
				continue;
			}

			$controller = new $controller_fqcn();
			if ( method_exists( $controller, 'register_routes' ) ) {
				$controller->register_routes();
			}
		}
	}
}
