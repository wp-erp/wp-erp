<?php
/**
 * WP-ERP HR: `erp/v2/onboarding` (setup wizard).
 *
 * Backs the React setup wizard on `index.php?page=erp-setup`:
 *   GET  /onboarding              Hydrated values for every step + the lists the
 *                                 steps choose from.
 *   POST /onboarding/save-step    Saves the keys a step sends (a partial patch).
 *   POST /onboarding/complete     Creates the selected leave policies, marks setup done.
 *   POST /onboarding/sample-data  Fills the site with sample HR data (once).
 *   GET  /onboarding/checklist    The HR dashboard's setup checklist (done / to do).
 *   POST /onboarding/checklist/dismiss  Hides that checklist for the current user.
 *
 * Every route is for site administrators: the wizard writes company settings.
 *
 * @since 1.18.0
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\CLI\Seed\DataProvider;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class OnboardingController extends RestController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'onboarding';

	/**
	 * Set once the wizard has been finished at least once.
	 */
	const COMPLETED_OPTION = 'erp_onboarding_completed';

	/**
	 * Leave types the admin picked on the Leave step (predefined slugs or `custom_<id>`).
	 */
	const LEAVE_TYPES_OPTION = 'erp_onboarding_selected_leave_types';

	/**
	 * Held while sample data loads, so a double click cannot seed twice.
	 */
	const SAMPLE_DATA_LOCK = 'erp_onboarding_sample_data_loading';

	/**
	 * Sample people use this reserved domain, so no email reaches anyone and
	 * a second load can tell the sample is already there.
	 */
	const SAMPLE_EMAIL_DOMAIN = 'wperp-sample.test';

	const SAMPLE_EMPLOYEES = 20;

	/**
	 * User meta that hides the dashboard setup checklist for one person.
	 */
	const CHECKLIST_DISMISSED_META = 'erp_hr_setup_checklist_dismissed';

	const WEEKDAYS = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];

	const MONTHS = [ 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december' ];

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_status' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/save-step',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_step' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/complete',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'complete' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/checklist',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_checklist' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/checklist/dismiss',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'dismiss_checklist' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/sample-data',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'load_sample_data' ],
					'permission_callback' => [ $this, 'permission_sample_data' ],
				],
			]
		);
	}

	/**
	 * The wizard changes company-wide settings: administrators only.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sample data creates WordPress accounts, so it also needs the create-employee cap.
	 *
	 * @return bool
	 */
	public function permission_sample_data(): bool {
		return $this->permission_manage() && current_user_can( 'erp_create_employee' );
	}

	/**
	 * GET: every step's stored values plus the option lists.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_status( $request ): WP_REST_Response {
		$leave_types = array_map(
			static function ( $type ) {
				return [
					'id'   => $type['id'],
					'name' => $type['name'],
				];
			},
			$this->leave_type_options()
		);

		return rest_ensure_response(
			[
				'completed' => '1' === (string) get_option( self::COMPLETED_OPTION, '' ),
				'values'    => $this->current_values(),
				'meta'      => [
					'leaveTypes'    => $leave_types,
					'currencies'    => $this->currency_options(),
					'dateFormats'   => $this->date_format_options(),
					'businessTypes' => $this->business_type_options(),
				],
			]
		);
	}

	/**
	 * POST: save whichever step keys arrive. Keys that are absent stay untouched,
	 * so each step only writes its own fields.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_step( $request ) {
		$data = (array) $request->get_json_params();

		// Basic step.
		if ( array_key_exists( 'companyName', $data ) || array_key_exists( 'businessType', $data ) ) {
			$company = new \WeDevs\ERP\Company();
			$update  = [];

			if ( array_key_exists( 'companyName', $data ) ) {
				$name = sanitize_text_field( (string) $data['companyName'] );

				if ( '' === $name ) {
					return new WP_Error( 'rest_invalid_param', __( 'Company name is required.', 'erp' ), [ 'status' => 400 ] );
				}

				$update['name'] = $name;
			}

			if ( array_key_exists( 'businessType', $data ) ) {
				$type = (string) $data['businessType'];

				if ( '' !== $type && ! array_key_exists( $type, $this->business_types() ) ) {
					return new WP_Error( 'rest_invalid_param', __( 'Choose a business type from the list.', 'erp' ), [ 'status' => 400 ] );
				}

				$update['business_type'] = $type;
			}

			$company->update( $update );
		}

		$general = get_option( 'erp_settings_general', [] );
		$general = is_array( $general ) ? $general : [];
		$touched = false;

		if ( array_key_exists( 'financialYearStarts', $data ) ) {
			$month = array_search( strtolower( (string) $data['financialYearStarts'] ), self::MONTHS, true );

			$general['gen_financial_month'] = false === $month ? '1' : (string) ( $month + 1 );
			$touched                        = true;
		}

		if ( array_key_exists( 'companyStartDate', $data ) ) {
			$date = (string) $data['companyStartDate'];

			if ( '' === $date || ! $this->is_ymd( $date ) ) {
				return new WP_Error( 'rest_invalid_param', __( 'Company start date is required.', 'erp' ), [ 'status' => 400 ] );
			}

			$general['gen_com_start'] = $date;
			$touched                  = true;
		}

		if ( array_key_exists( 'currency', $data ) ) {
			$currency = (string) $data['currency'];

			if ( ! array_key_exists( $currency, $this->currencies() ) ) {
				return new WP_Error( 'rest_invalid_param', __( 'Choose a currency from the list.', 'erp' ), [ 'status' => 400 ] );
			}

			$general['erp_currency'] = $currency;
			$touched                 = true;
		}

		if ( array_key_exists( 'dateFormat', $data ) ) {
			$format = (string) $data['dateFormat'];

			if ( ! array_key_exists( $format, $this->date_formats() ) ) {
				return new WP_Error( 'rest_invalid_param', __( 'Choose a date format from the list.', 'erp' ), [ 'status' => 400 ] );
			}

			$general['date_format'] = $format;
			$touched                = true;
		}

		if ( $touched ) {
			update_option( 'erp_settings_general', $general );
		}

		// Appsero "Share Essentials". Only a changed (or first) answer is sent:
		// opting in posts the site data to Appsero, so re-saving the step must
		// not post it again.
		if ( array_key_exists( 'shareEssentials', $data ) && class_exists( '\WeDevs\ERP\Tracker' ) ) {
			$tracker = \WeDevs\ERP\Tracker::get_instance();
			$allow   = rest_sanitize_boolean( $data['shareEssentials'] );

			if ( ! $tracker->answered() || $allow === $tracker->not_allowed() ) {
				if ( $allow ) {
					$tracker->optin();
				} else {
					$tracker->optout();
				}
			}
		}

		// Departments / designations: add the ones not there yet (case-insensitive).
		if ( isset( $data['departments'] ) && is_array( $data['departments'] ) ) {
			$existing = array_map(
				static function ( $dept ) {
					return strtolower( trim( $dept->title ) );
				},
				erp_hr_get_departments( [ 'number' => -1 ] )
			);

			foreach ( $data['departments'] as $title ) {
				$this->add_org_item( (string) $title, $existing, 'erp_hr_create_department' );
			}
		}

		if ( isset( $data['designations'] ) && is_array( $data['designations'] ) ) {
			$existing = array_map(
				static function ( $desig ) {
					return strtolower( trim( $desig->title ) );
				},
				erp_hr_get_designations( [ 'number' => -1 ] )
			);

			foreach ( $data['designations'] as $title ) {
				$this->add_org_item( (string) $title, $existing, 'erp_hr_create_designation' );
			}
		}

		// Leave step: leave years (id-stable upsert, same as Settings) + chosen types.
		if ( isset( $data['leaveYears'] ) && is_array( $data['leaveYears'] ) ) {
			$years = [];

			foreach ( $data['leaveYears'] as $year ) {
				if ( ! is_array( $year ) ) {
					continue;
				}

				$row = [
					'fy_name'     => sanitize_text_field( (string) ( $year['fy_name'] ?? '' ) ),
					'start_date'  => (string) ( $year['start_date'] ?? '' ),
					'end_date'    => (string) ( $year['end_date'] ?? '' ),
					'description' => __( 'Year for leave', 'erp' ),
				];

				if ( ! $this->is_ymd( $row['start_date'] ) || ! $this->is_ymd( $row['end_date'] ) ) {
					return new WP_Error( 'rest_invalid_param', __( 'Start and end date are required for every leave year.', 'erp' ), [ 'status' => 400 ] );
				}

				if ( ! empty( $year['id'] ) ) {
					$row['id'] = absint( $year['id'] );
				}

				$years[] = $row;
			}

			// Two leave years may not share a day: leave balances are counted per
			// year. A saved year left out of the list stays when leave records
			// still point at it, so it counts too.
			$sent_ids = array_filter(
				array_map(
					static function ( $row ) {
						return (int) ( $row['id'] ?? 0 );
					},
					$years
				)
			);
			$kept     = array_filter(
				erp_get_hr_financial_years(),
				static function ( $fy ) use ( $sent_ids ) {
					return ! in_array( (int) $fy['id'], $sent_ids, true ) && erp_hr_financial_year_in_use( (int) $fy['id'] );
				}
			);

			foreach ( $years as $i => $a ) {
				foreach ( array_merge( array_slice( $years, $i + 1 ), array_values( $kept ) ) as $b ) {
					if ( $a['start_date'] <= $b['end_date'] && $b['start_date'] <= $a['end_date'] ) {
						return new WP_Error(
							'rest_invalid_param',
							/* translators: 1: leave year name, 2: leave year name. */
							sprintf( __( 'Leave years %1$s and %2$s overlap. Each day can belong to one leave year only.', 'erp' ), $a['fy_name'], $b['fy_name'] ),
							[ 'status' => 400 ]
						);
					}
				}
			}

			if ( ! empty( $years ) ) {
				$saved = erp_settings_save_leave_years( $years );

				if ( is_wp_error( $saved ) ) {
					$saved->add_data( [ 'status' => 400 ] );
					return $saved;
				}
			}
		}

		if ( isset( $data['selectedLeaveTypes'] ) && is_array( $data['selectedLeaveTypes'] ) ) {
			$allowed  = wp_list_pluck( $this->leave_type_options(), 'id' );
			$selected = array_values( array_intersect( array_map( 'strval', $data['selectedLeaveTypes'] ), $allowed ) );

			update_option( self::LEAVE_TYPES_OPTION, $selected );
		}

		// Workday step: only the seven weekday keys, only 0 / 4 / 8 hours.
		if ( isset( $data['workingDays'] ) && is_array( $data['workingDays'] ) ) {
			$days = [];

			foreach ( self::WEEKDAYS as $day ) {
				$hours = isset( $data['workingDays'][ $day ] ) ? (string) $data['workingDays'][ $day ] : '';

				if ( ! in_array( $hours, [ '0', '4', '8' ], true ) ) {
					return new WP_Error( 'rest_invalid_param', __( 'Choose full, half or non-working for every day.', 'erp' ), [ 'status' => 400 ] );
				}

				$days[ $day ] = $hours;
			}

			foreach ( $days as $day => $hours ) {
				update_option( $day, $hours );
			}

			update_option( 'erp_settings_erp-hr_workdays', $days );
		}

		if ( isset( $data['workingHours'] ) && is_array( $data['workingHours'] ) ) {
			$start = (string) ( $data['workingHours']['start'] ?? '' );
			$end   = (string) ( $data['workingHours']['end'] ?? '' );

			if ( ! $this->is_hm( $start ) || ! $this->is_hm( $end ) ) {
				return new WP_Error( 'rest_invalid_param', __( 'Office hours need a start and an end time.', 'erp' ), [ 'status' => 400 ] );
			}

			if ( $end <= $start ) {
				return new WP_Error( 'rest_invalid_param', __( 'Office end time must be after the start time.', 'erp' ), [ 'status' => 400 ] );
			}

			update_option(
				'erp_working_hours',
				[
					'start' => $start,
					'end'   => $end,
				]
			);
		}

		return rest_ensure_response(
			[
				'success' => true,
				'values'  => $this->current_values(),
			]
		);
	}

	/**
	 * POST: create the policies for the chosen leave types in the current leave
	 * year, then mark setup done. Types that already have a policy in that year
	 * are skipped, so running setup again never duplicates them.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function complete( $request ): WP_REST_Response {
		$selected = get_option( self::LEAVE_TYPES_OPTION, [] );
		$f_year   = $this->current_financial_year_id();

		if ( $f_year && is_array( $selected ) && ! empty( $selected ) ) {
			$this->create_leave_policies( $f_year, $selected );
		}

		update_option( self::COMPLETED_OPTION, '1' );
		update_option( 'erp_setup_wizard_ran', '1' );

		return rest_ensure_response(
			[
				'success'      => true,
				'redirect_url' => admin_url( 'admin.php?page=erp-hr#/employees' ),
			]
		);
	}

	/**
	 * POST: fill the site with sample data: departments, designations, a leave
	 * year with types, policies and holidays, twenty employees with entitlements,
	 * and a few pending leave requests. Loaded once; a second call, or one while
	 * the first still runs, is refused.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function load_sample_data( $request ) {
		if ( $this->sample_user_ids( 1 ) ) {
			return new WP_Error( 'erp_sample_data_exists', __( 'Sample data is already on this site.', 'erp' ), [ 'status' => 409 ] );
		}

		if ( get_transient( self::SAMPLE_DATA_LOCK ) ) {
			return new WP_Error( 'erp_sample_data_running', __( 'Sample data is already being loaded. Give it a minute.', 'erp' ), [ 'status' => 409 ] );
		}

		set_transient( self::SAMPLE_DATA_LOCK, 1, 5 * MINUTE_IN_SECONDS );

		if ( function_exists( 'set_time_limit' ) ) {
			// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- twenty account creations can pass a 30 second host default.
			set_time_limit( 120 );
		}

		// Sample accounts must not mail anyone (welcome emails, notifications).
		add_filter( 'pre_wp_mail', '__return_false' );

		try {
			$result = $this->seed_sample_data();
		} finally {
			remove_filter( 'pre_wp_mail', '__return_false' );
			delete_transient( self::SAMPLE_DATA_LOCK );
		}

		return rest_ensure_response( array_merge( [ 'success' => true ], $result ) );
	}

	/**
	 * GET: the dashboard setup checklist, each required setting and whether it
	 * is done. Every step is read from the data itself, never from what was
	 * clicked, so work done through the wizard, an import, sample data or the
	 * HR screens counts the same.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_checklist( $request ): WP_REST_Response {
		$steps     = $this->checklist_steps();
		$completed = count( array_filter( array_column( $steps, 'done' ) ) );

		return rest_ensure_response(
			[
				'steps'     => $steps,
				'completed' => $completed,
				'total'     => count( $steps ),
				'done'      => count( $steps ) === $completed,
				'dismissed' => (bool) get_user_meta( get_current_user_id(), self::CHECKLIST_DISMISSED_META, true ),
				'wizardUrl' => admin_url( 'index.php?page=erp-setup' ),
			]
		);
	}

	/**
	 * POST: hide the setup checklist for the current user.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function dismiss_checklist( $request ): WP_REST_Response {
		update_user_meta( get_current_user_id(), self::CHECKLIST_DISMISSED_META, 1 );

		return rest_ensure_response( [ 'dismissed' => true ] );
	}

	/**
	 * The checklist steps, in the order they are worth doing: the company, the
	 * leave year leave is counted in, the structure people sit in, the leave
	 * they can take, then the people.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function checklist_steps(): array {
		global $wpdb;

		$company = (string) ( new \WeDevs\ERP\Company() )->name;
		$now     = time();

		$steps = [
			[
				'key'         => 'company',
				'label'       => __( 'Add your company details', 'erp' ),
				'description' => __( 'The company name appears on emails, payslips and reports.', 'erp' ),
				'action'      => __( 'Open settings', 'erp' ),
				'url'         => admin_url( 'admin.php?page=erp-settings' ),
				'done'        => '' !== trim( $company ) && __( 'Untitled Company', 'erp' ) !== $company,
			],
			[
				'key'         => 'leave_year',
				'label'       => __( 'Set up the current leave year', 'erp' ),
				'description' => __( 'Leave is counted inside a leave year; without one covering today nobody can request leave.', 'erp' ),
				'action'      => __( 'Add leave year', 'erp' ),
				'route'       => '/leave/financial-years',
				'done'        => $this->has_row( $wpdb->prefix . 'erp_hr_financial_years', $wpdb->prepare( 'start_date <= %d AND end_date >= %d', $now, $now ) ),
			],
			[
				'key'         => 'departments',
				'label'       => __( 'Add your departments', 'erp' ),
				'description' => __( 'Departments group people and decide who approves their leave.', 'erp' ),
				'action'      => __( 'Add department', 'erp' ),
				'route'       => '/departments',
				'done'        => $this->has_row( $wpdb->prefix . 'erp_hr_depts' ),
			],
			[
				'key'         => 'designations',
				'label'       => __( 'Add your designations', 'erp' ),
				'description' => __( 'Job titles appear on profiles, reports and payslips.', 'erp' ),
				'action'      => __( 'Add designation', 'erp' ),
				'route'       => '/designations',
				'done'        => $this->has_row( $wpdb->prefix . 'erp_hr_designations' ),
			],
			[
				'key'         => 'leave_policies',
				'label'       => __( 'Create your leave policies', 'erp' ),
				'description' => __( 'A policy gives people their days off; without one there is no leave to request.', 'erp' ),
				'action'      => __( 'Add policy', 'erp' ),
				'route'       => '/leave/policies',
				'done'        => $this->has_row( $wpdb->prefix . 'erp_hr_leave_policies' ),
			],
			[
				'key'         => 'holidays',
				'label'       => __( 'Add your holidays', 'erp' ),
				'description' => __( 'Holidays are left out of the days a leave request costs.', 'erp' ),
				'action'      => __( 'Add holiday', 'erp' ),
				'route'       => '/leave/holidays',
				'done'        => $this->has_row( $wpdb->prefix . 'erp_hr_holiday' ),
			],
			[
				'key'         => 'employees',
				'label'       => __( 'Add your employees', 'erp' ),
				'description' => __( 'Add people one by one or import them from a CSV file.', 'erp' ),
				'action'      => __( 'Add employees', 'erp' ),
				'route'       => '/employees',
				'done'        => $this->has_row( $wpdb->prefix . 'erp_hr_employees', $wpdb->prepare( 'user_id <> %d', get_current_user_id() ) ),
			],
		];

		/**
		 * Filters the HR dashboard setup checklist.
		 *
		 * An add-on with something worth setting up early adds a step here. Each
		 * needs a key, label, description, action, a `route` inside the HR app or
		 * an absolute `url`, and a `done` flag read from live data.
		 *
		 * @since 1.18.0
		 *
		 * @param array<int, array<string, mixed>> $steps The checklist steps.
		 */
		$steps = apply_filters( 'erp_hr_setup_checklist_steps', $steps );

		return array_values( array_filter( (array) $steps, 'is_array' ) );
	}

	/**
	 * Whether a table has at least one row (matching a prepared condition).
	 * Asks for one row instead of counting, so a large company costs the same.
	 *
	 * @param string $table Full table name (an internal constant, not input).
	 * @param string $where Prepared WHERE fragment.
	 *
	 * @return bool
	 */
	private function has_row( string $table, string $where = '1=1' ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return null !== $wpdb->get_var( "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1" );
	}

	/**
	 * Every step's stored values, in the shape the wizard edits.
	 *
	 * @return array
	 */
	private function current_values(): array {
		$company = new \WeDevs\ERP\Company();
		$general = get_option( 'erp_settings_general', [] );
		$general = is_array( $general ) ? $general : [];

		$month = (int) ( $general['gen_financial_month'] ?? 1 );
		$month = self::MONTHS[ $month - 1 ] ?? 'january';

		$leave_years = array_map(
			static function ( $fy ) {
				return [
					'id'         => (int) $fy['id'],
					'fy_name'    => (string) $fy['fy_name'],
					'start_date' => (string) $fy['start_date'],
					'end_date'   => (string) $fy['end_date'],
				];
			},
			erp_get_hr_financial_years()
		);

		$working_days = [];

		foreach ( erp_hr_get_work_days() as $day => $hours ) {
			$working_days[ $day ] = (string) $hours;
		}

		$hours = get_option( 'erp_working_hours', [] );

		$currency = (string) ( $general['erp_currency'] ?? '' );
		$format   = (string) ( $general['date_format'] ?? '' );

		return [
			'companyName'         => (string) $company->name,
			'companyStartDate'    => (string) ( $general['gen_com_start'] ?? '' ),
			'financialYearStarts' => $month,
			'currency'            => '' !== $currency ? $currency : '148',
			'dateFormat'          => '' !== $format ? $format : 'd-m-Y',
			'businessType'        => (string) $company->business_type,
			'shareEssentials'     => $this->share_essentials(),
			'departments'         => array_values( wp_list_pluck( erp_hr_get_departments( [ 'number' => -1 ] ), 'title' ) ),
			'designations'        => array_values( wp_list_pluck( erp_hr_get_designations( [ 'number' => -1 ] ), 'title' ) ),
			'leaveYears'          => $leave_years,
			'selectedLeaveTypes'  => $this->selected_leave_types(),
			'workingDays'         => $working_days,
			'workingHours'        => [
				'start' => (string) ( $hours['start'] ?? '09:00' ),
				'end'   => (string) ( $hours['end'] ?? '17:00' ),
			],
		];
	}

	/**
	 * The consent toggle's value: what the admin answered before, or on by
	 * default for a site that never answered (as the old setup wizard did).
	 *
	 * @return bool
	 */
	private function share_essentials(): bool {
		if ( ! class_exists( '\WeDevs\ERP\Tracker' ) ) {
			return false;
		}

		$tracker = \WeDevs\ERP\Tracker::get_instance();

		return ! $tracker->not_allowed() || ! $tracker->answered();
	}

	/**
	 * The chosen leave types: the saved selection, or the types that already
	 * have a policy on a site that set leave up before, or none (the wizard
	 * then selects all).
	 *
	 * @return array
	 */
	private function selected_leave_types(): array {
		$saved = get_option( self::LEAVE_TYPES_OPTION, null );

		if ( is_array( $saved ) ) {
			return array_values( array_map( 'strval', $saved ) );
		}

		global $wpdb;

		$with_policy = $wpdb->get_results(
			"SELECT DISTINCT l.id, l.name FROM {$wpdb->prefix}erp_hr_leave_policies p
			 JOIN {$wpdb->prefix}erp_hr_leaves l ON p.leave_id = l.id"
		);

		$by_name = [];

		foreach ( $this->predefined_leave_types() as $type ) {
			$by_name[ strtolower( $type['name'] ) ] = $type['id'];
		}

		$selected = [];

		foreach ( (array) $with_policy as $leave ) {
			$key        = strtolower( trim( $leave->name ) );
			$selected[] = $by_name[ $key ] ?? 'custom_' . (int) $leave->id;
		}

		return $selected;
	}

	/**
	 * The predefined leave types, then any other leave type already on the site.
	 *
	 * @return array<int, array{id: string, name: string, db_id: ?int}>
	 */
	private function leave_type_options(): array {
		global $wpdb;

		$existing = (array) $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}erp_hr_leaves ORDER BY name ASC" );
		$db_ids   = [];

		foreach ( $existing as $leave ) {
			$db_ids[ strtolower( trim( $leave->name ) ) ] = (int) $leave->id;
		}

		$options = [];

		foreach ( $this->predefined_leave_types() as $type ) {
			$key       = strtolower( $type['name'] );
			$options[] = [
				'id'    => $type['id'],
				'name'  => $type['name'],
				'db_id' => $db_ids[ $key ] ?? null,
			];
			unset( $db_ids[ $key ] );
		}

		foreach ( $existing as $leave ) {
			if ( isset( $db_ids[ strtolower( trim( $leave->name ) ) ] ) ) {
				$options[] = [
					'id'    => 'custom_' . (int) $leave->id,
					'name'  => (string) $leave->name,
					'db_id' => (int) $leave->id,
				];
			}
		}

		return $options;
	}

	/**
	 * Predefined leave types: the same list the HR seeder uses.
	 *
	 * @return array
	 */
	private function predefined_leave_types(): array {
		return [
			[ 'id' => 'annual_leave', 'name' => __( 'Annual Leave', 'erp' ), 'days' => 20, 'color' => '#4CAF50', 'description' => __( 'Paid vacation days for personal time off', 'erp' ) ],
			[ 'id' => 'sick_leave', 'name' => __( 'Sick Leave', 'erp' ), 'days' => 14, 'color' => '#F44336', 'description' => __( 'Leave for illness or medical appointments', 'erp' ) ],
			[ 'id' => 'casual_leave', 'name' => __( 'Casual Leave', 'erp' ), 'days' => 10, 'color' => '#2196F3', 'description' => __( 'Short-notice leave for personal matters', 'erp' ) ],
			[ 'id' => 'maternity_leave', 'name' => __( 'Maternity Leave', 'erp' ), 'days' => 90, 'color' => '#E91E63', 'description' => __( 'Leave for expecting mothers', 'erp' ) ],
			[ 'id' => 'paternity_leave', 'name' => __( 'Paternity Leave', 'erp' ), 'days' => 10, 'color' => '#9C27B0', 'description' => __( 'Leave for new fathers', 'erp' ) ],
		];
	}

	/**
	 * The leave year that covers today, else the earliest one.
	 *
	 * @return int 0 when no leave year exists.
	 */
	private function current_financial_year_id(): int {
		global $wpdb;

		$now   = time();
		$table = $wpdb->prefix . 'erp_hr_financial_years';

		$id = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE start_date <= %d AND end_date >= %d ORDER BY start_date ASC LIMIT 1", $now, $now )
		);

		if ( ! $id ) {
			$id = (int) $wpdb->get_var( "SELECT id FROM {$table} ORDER BY start_date ASC LIMIT 1" );
		}

		return $id;
	}

	/**
	 * Create a policy for each chosen leave type in the given leave year, skipping
	 * types that already have one there.
	 *
	 * @param int   $f_year   Leave year id.
	 * @param array $selected Predefined slugs and `custom_<leave id>` values.
	 *
	 * @return array<int, int> Leave id => policy id, for the policies now in that year.
	 */
	private function create_leave_policies( int $f_year, array $selected ): array {
		global $wpdb;

		$existing = [];

		foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT id, leave_id FROM {$wpdb->prefix}erp_hr_leave_policies WHERE f_year = %d", $f_year ) ) as $row ) {
			$existing[ (int) $row->leave_id ] = (int) $row->id;
		}

		$selected = array_map( 'strval', $selected );
		$types    = [];

		foreach ( $this->predefined_leave_types() as $type ) {
			if ( in_array( $type['id'], $selected, true ) ) {
				$types[] = $type;
			}
		}

		foreach ( $selected as $value ) {
			if ( 0 === strpos( $value, 'custom_' ) ) {
				$leave = \WeDevs\ERP\HRM\Models\Leave::find( (int) substr( $value, 7 ) );

				if ( $leave ) {
					$types[] = [
						'leave_id'    => (int) $leave->id,
						'name'        => (string) $leave->name,
						'days'        => 0,
						'color'       => '#6B7280',
						'description' => '',
					];
				}
			}
		}

		foreach ( $types as $type ) {
			$leave_id = $type['leave_id'] ?? 0;

			if ( ! $leave_id ) {
				$found    = \WeDevs\ERP\HRM\Models\Leave::where( 'name', $type['name'] )->first();
				$leave_id = $found ? (int) $found->id : erp_hr_insert_leave_policy_name(
					[
						'name'        => $type['name'],
						'description' => $type['description'],
					]
				);
			}

			if ( is_wp_error( $leave_id ) || ! $leave_id || isset( $existing[ (int) $leave_id ] ) ) {
				continue;
			}

			$policy_id = erp_hr_leave_insert_policy(
				[
					'leave_id'            => (int) $leave_id,
					'employee_type'       => -1,
					'department_id'       => -1,
					'designation_id'      => -1,
					'location_id'         => -1,
					'gender'              => -1,
					'marital'             => -1,
					'f_year'              => $f_year,
					'description'         => $type['description'],
					'days'                => $type['days'],
					'color'               => $type['color'],
					'applicable_from'     => 0,
					'apply_for_new_users' => 1,
				]
			);

			if ( ! is_wp_error( $policy_id ) && $policy_id ) {
				$existing[ (int) $leave_id ] = (int) $policy_id;
			}
		}

		return $existing;
	}

	/**
	 * Seed the sample company.
	 *
	 * @return array Counts of what was created.
	 */
	private function seed_sample_data(): array {
		global $wpdb;

		// Org structure.
		$this->save_step_values(
			[
				'departments'  => wp_list_pluck( DataProvider::departments(), 'title' ),
				'designations' => wp_list_pluck( DataProvider::designations(), 'title' ),
			]
		);

		$departments  = array_map( 'intval', wp_list_pluck( erp_hr_get_departments( [ 'number' => -1 ] ), 'id' ) );
		$designations = array_map( 'intval', wp_list_pluck( erp_hr_get_designations( [ 'number' => -1 ] ), 'id' ) );

		// A leave year covering today.
		$f_year = $this->current_financial_year_id();
		$year   = (int) wp_date( 'Y' );

		if ( ! $f_year || ! $this->year_covers_today( $f_year ) ) {
			$years   = erp_get_hr_financial_years();
			$years[] = [
				'fy_name'    => (string) $year,
				'start_date' => $year . '-01-01',
				'end_date'   => $year . '-12-31',
			];

			erp_settings_save_leave_years( $years );
			$f_year = $this->current_financial_year_id();
		}

		// Leave types and policies (all predefined types).
		$policies = $f_year ? $this->create_leave_policies( $f_year, wp_list_pluck( $this->predefined_leave_types(), 'id' ) ) : [];

		// Holidays for this year.
		$holidays = 0;

		foreach ( DataProvider::holidays() as $holiday ) {
			$date   = sprintf( '%d-%02d-%02d', $year, $holiday['month'], $holiday['day'] );
			$result = erp_hr_leave_insert_holiday(
				[
					'title'       => $holiday['title'],
					'start'       => $date,
					'end'         => $date,
					'description' => $holiday['title'],
				]
			);

			if ( ! is_wp_error( $result ) ) {
				++$holidays;
			}
		}

		// Employees.
		$male    = DataProvider::first_names_male();
		$female  = DataProvider::first_names_female();
		$last    = DataProvider::last_names();
		$types   = [ 'permanent', 'permanent', 'permanent', 'parttime', 'contract' ];
		$people  = [];

		for ( $i = 0; $i < self::SAMPLE_EMPLOYEES; $i++ ) {
			$gender = 0 === $i % 2 ? 'male' : 'female';
			$first  = 'male' === $gender ? $male[ $i % count( $male ) ] : $female[ $i % count( $female ) ];
			$surname = $last[ ( $i * 7 ) % count( $last ) ];
			$email  = strtolower( preg_replace( '/[^a-z]/i', '', $first ) . '.' . preg_replace( '/[^a-z]/i', '', $surname ) ) . '@' . self::SAMPLE_EMAIL_DOMAIN;

			if ( email_exists( $email ) ) {
				$email = str_replace( '@', '.' . ( $i + 1 ) . '@', $email );
			}

			$employee = new \WeDevs\ERP\HRM\Employee( null );
			$result   = $employee->create_employee(
				[
					'user_email' => $email,
					'personal'   => [
						'first_name'     => $first,
						'last_name'      => $surname,
						'gender'         => $gender,
						'marital_status' => 0 === $i % 3 ? 'married' : 'single',
					],
					'work'       => [
						'designation' => $designations ? $designations[ $i % count( $designations ) ] : 0,
						'department'  => $departments ? $departments[ $i % count( $departments ) ] : 0,
						'hiring_date' => wp_date( 'Y-m-d', strtotime( '-' . ( 30 + $i * 25 ) . ' days' ) ),
						'pay_rate'    => 3000 + $i * 250,
						'pay_type'    => 'monthly',
						'type'        => $types[ $i % count( $types ) ],
						'status'      => 'active',
					],
				]
			);

			if ( ! is_wp_error( $result ) ) {
				$people[] = is_object( $result ) ? (int) $result->get_user_id() : (int) $result;
			}
		}

		// Entitlements for every sample employee on every policy, then one
		// pending request for the first eight of them.
		$requests = 0;

		foreach ( $people as $index => $user_id ) {
			foreach ( $policies as $policy_id ) {
				$policy = \WeDevs\ERP\HRM\Models\LeavePolicy::find( $policy_id );

				if ( ! $policy ) {
					continue;
				}

				erp_hr_leave_insert_entitlement(
					[
						'user_id'     => $user_id,
						'leave_id'    => (int) $policy->leave_id,
						'created_by'  => get_current_user_id(),
						'trn_id'      => (int) $policy->id,
						'trn_type'    => 'leave_policies',
						'day_in'      => (float) $policy->days,
						'day_out'     => 0,
						'description' => __( 'Sample data', 'erp' ),
						'f_year'      => (int) $policy->f_year,
					]
				);
			}

			if ( $index >= 8 ) {
				continue;
			}

			$entitlement = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE user_id = %d AND trn_type = 'leave_policies' AND f_year = %d ORDER BY id ASC LIMIT 1",
					$user_id,
					$f_year
				)
			);

			if ( ! $entitlement ) {
				continue;
			}

			$start = $this->next_weekday( strtotime( '+' . ( 7 + $index * 3 ) . ' days' ) );

			if ( (int) wp_date( 'Y', $start ) !== $year ) {
				continue;
			}

			$result = erp_hr_leave_insert_request(
				[
					'user_id'      => $user_id,
					'leave_policy' => (int) $entitlement,
					'start_date'   => wp_date( 'Y-m-d', $start ),
					'end_date'     => wp_date( 'Y-m-d', $start ),
					'reason'       => DataProvider::leave_reasons()[ $index % count( DataProvider::leave_reasons() ) ],
					'status'       => 2,
				]
			);

			if ( ! is_wp_error( $result ) ) {
				++$requests;
			}
		}

		return [
			'employees'    => count( $people ),
			'departments'  => count( $departments ),
			'leave_types'  => count( $policies ),
			'holidays'     => $holidays,
			'requests'     => $requests,
		];
	}

	/**
	 * Run the org part of save_step for the sample seeder.
	 *
	 * @param array $values Step values.
	 *
	 * @return void
	 */
	private function save_step_values( array $values ): void {
		$request = new WP_REST_Request( 'POST', '/' . $this->namespace . '/' . $this->rest_base . '/save-step' );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( $values ) );

		$this->save_step( $request );
	}

	/**
	 * Create a department or designation unless one with that name exists.
	 *
	 * @param string   $title    Raw title.
	 * @param array    $existing Lower-cased titles already present (updated in place).
	 * @param callable $create   Creator taking `[ 'title' => … ]`.
	 *
	 * @return void
	 */
	private function add_org_item( string $title, array &$existing, callable $create ): void {
		$title = sanitize_text_field( $title );
		$key   = strtolower( trim( $title ) );

		if ( '' === $key || in_array( $key, $existing, true ) ) {
			return;
		}

		$result = $create( [ 'title' => $title ] );

		if ( ! is_wp_error( $result ) ) {
			$existing[] = $key;
		}
	}

	/**
	 * Ids of sample accounts on the site.
	 *
	 * @param int $limit Max ids.
	 *
	 * @return array
	 */
	private function sample_user_ids( int $limit = -1 ): array {
		return get_users(
			[
				'search'         => '*@' . self::SAMPLE_EMAIL_DOMAIN,
				'search_columns' => [ 'user_email' ],
				'fields'         => 'ID',
				'number'         => $limit,
			]
		);
	}

	/**
	 * Whether a leave year contains today.
	 *
	 * @param int $f_year Leave year id.
	 *
	 * @return bool
	 */
	private function year_covers_today( int $f_year ): bool {
		global $wpdb;

		$now = time();

		return (bool) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_hr_financial_years WHERE id = %d AND start_date <= %d AND end_date >= %d", $f_year, $now, $now )
		);
	}

	/**
	 * The given day, moved forward past a weekend.
	 *
	 * @param int $timestamp Timestamp.
	 *
	 * @return int
	 */
	private function next_weekday( int $timestamp ): int {
		$dow = (int) wp_date( 'N', $timestamp );

		return $dow >= 6 ? strtotime( '+' . ( 8 - $dow ) . ' days', $timestamp ) : $timestamp;
	}

	/**
	 * Currencies the site knows, id => label (`erp_settings_general.erp_currency` stores the id).
	 *
	 * @return array
	 */
	private function currencies(): array {
		return function_exists( 'erp_get_currencies_for_dropdown' ) ? (array) erp_get_currencies_for_dropdown() : [];
	}

	/**
	 * Date formats offered, PHP format => example.
	 *
	 * @return array
	 */
	private function date_formats(): array {
		return [
			'm-d-Y' => 'mm-dd-yyyy',
			'd-m-Y' => 'dd-mm-yyyy',
			'm/d/Y' => 'mm/dd/yyyy',
			'd/m/Y' => 'dd/mm/yyyy',
			'Y-m-d' => 'yyyy-mm-dd',
		];
	}

	/**
	 * Business types, as the old setup wizard offered them.
	 *
	 * @return array
	 */
	private function business_types(): array {
		return [
			'Freelance'        => __( 'Freelance', 'erp' ),
			'FreelanceDev'     => __( 'Freelance (Developer)', 'erp' ),
			'FreelanceDes'     => __( 'Freelance (Design)', 'erp' ),
			'SmallBLocal'      => __( 'Small Business: Local Service (e.g. Hairdresser)', 'erp' ),
			'SmallBWeb'        => __( 'Small Business: Web Business', 'erp' ),
			'SmallBOther'      => __( 'Small Business (Other)', 'erp' ),
			'ecommerceWoo'     => __( 'eCommerce (WooCommerce)', 'erp' ),
			'ecommerceShopify' => __( 'eCommerce (Shopify)', 'erp' ),
			'ecommerceOther'   => __( 'eCommerce (Other)', 'erp' ),
			'Other'            => __( 'Other', 'erp' ),
		];
	}

	/**
	 * `{value, label}` list from an id => label map.
	 *
	 * @param array $map Map.
	 *
	 * @return array
	 */
	private function to_choices( array $map ): array {
		$choices = [];

		foreach ( $map as $value => $label ) {
			$choices[] = [
				'value' => (string) $value,
				'label' => (string) $label,
			];
		}

		return $choices;
	}

	private function currency_options(): array {
		return $this->to_choices( $this->currencies() );
	}

	private function date_format_options(): array {
		return $this->to_choices( $this->date_formats() );
	}

	private function business_type_options(): array {
		return $this->to_choices( $this->business_types() );
	}

	private function is_ymd( string $value ): bool {
		$date = \DateTime::createFromFormat( 'Y-m-d', $value );

		return $date && $date->format( 'Y-m-d' ) === $value;
	}

	private function is_hm( string $value ): bool {
		return (bool) preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value );
	}
}
