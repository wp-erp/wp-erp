<?php
/**
 * WP-ERP HR — UI engine resolver.
 *
 * Decides whether each redesigned HR admin page is served by the new React
 * engine or the legacy Vue/jQuery engine.
 *
 * The site-wide default is install-aware: a brand-new install starts on React,
 * while a site that upgraded into the redesign stays on the UI it already knows
 * until someone opts in (stamped once as `erp_hr_ui_default_engine`). Users then
 * switch either way per page via a nonce-verified switch action; the preference
 * is stored in user-meta. Operators can force one engine for everybody via the
 * `erp_hr_ui_engine` site option.
 *
 * Contract verified at openspec/changes/redesign-hr-free/ui-coexistence.md
 * (Resolution flow, Server-side wiring).
 */

namespace WeDevs\ERP\HRM\Admin;

defined( 'ABSPATH' ) || exit;

final class UiEngineResolver {

	public const SWITCH_ACTION  = 'switch_ui';
	public const NONCE_NAME     = 'erp_switch_ui';
	public const USERMETA_KEY   = 'erp_hr_ui_pref';
	public const SITE_OPTION    = 'erp_hr_ui_engine';
	public const DEFAULT_OPTION = 'erp_hr_ui_default_engine';

	public const ENGINE_REACT  = 'react';
	public const ENGINE_LEGACY = 'vue';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	private function __construct() {}

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook the admin_init handler for the switch action.
	 */
	public function register_hooks(): void {
		add_action( 'admin_init', [ $this, 'handle_switch' ] );
		add_action( 'admin_init', [ $this, 'prevent_engine_page_cache' ] );
	}

	/**
	 * Send no-cache headers on HR admin pages so the browser never serves a
	 * stale document for the *other* engine after a switch. Runs on admin_init
	 * (before headers are sent). Without this the post-switch redirect can land
	 * on a cached page and the new engine only appears after a manual refresh.
	 */
	public function prevent_engine_page_cache(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( $this->is_hr_page( $page ) && ! headers_sent() ) {
			nocache_headers();
		}
	}

	/**
	 * Site-wide default engine for users who never touched the switch.
	 *
	 * `erp_hr_ui_default_engine` is stamped once, at install time
	 * (WeDevsERPInstaller::activate()): 'react' for a brand-new install, 'vue'
	 * for a site that already had ERP. When the stamp is missing the plugin was
	 * updated in place without re-activation, which by definition means an
	 * existing site — keep it on the UI it knows and persist that decision so
	 * the derivation runs only once.
	 */
	public function default_engine(): string {
		$default = (string) get_option( self::DEFAULT_OPTION, '' );
		if ( in_array( $default, [ self::ENGINE_REACT, self::ENGINE_LEGACY ], true ) ) {
			return $default;
		}

		$default = get_option( 'wp_erp_version' ) ? self::ENGINE_LEGACY : self::ENGINE_REACT;

		update_option( self::DEFAULT_OPTION, $default );

		return $default;
	}

	/**
	 * Resolve which engine should render the given HR admin page slug.
	 *
	 * Resolution order (highest priority first):
	 *   1. Site option `erp_hr_ui_engine` if forced to 'react' or 'vue'.
	 *   2. User-meta `erp_hr_ui_pref[$key]` — 'legacy' → 'vue', 'react' → 'react'.
	 *   3. Install-aware site default (see default_engine()).
	 *
	 * The URL switch action is handled separately in handle_switch() and
	 * performs a redirect; it does not return here.
	 */
	public function resolve_engine( string $page_slug ): string {
		$forced = $this->forced_engine();
		if ( '' !== $forced ) {
			return $forced;
		}

		$default = $this->default_engine();

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $default;
		}

		$prefs = (array) get_user_meta( $user_id, self::USERMETA_KEY, true );
		$key   = $this->legacy_key_for_page( $page_slug );
		$pref  = (string) ( $prefs[ $key ] ?? '' );

		if ( 'legacy' === $pref ) {
			return self::ENGINE_LEGACY;
		}

		if ( self::ENGINE_REACT === $pref ) {
			return self::ENGINE_REACT;
		}

		return $default;
	}

	/**
	 * The engine an administrator has forced for everyone, or '' when the site
	 * option is left at `auto` and the per-user preference decides.
	 *
	 * @return string 'react', 'vue', or ''.
	 */
	public function forced_engine(): string {
		$forced = (string) get_option( self::SITE_OPTION, 'auto' );

		return in_array( $forced, [ self::ENGINE_REACT, self::ENGINE_LEGACY ], true ) ? $forced : '';
	}

	/**
	 * Whether a site-wide force is in effect, i.e. the per-user switch cannot
	 * change what anyone sees.
	 *
	 * @return bool
	 */
	public function is_engine_forced(): bool {
		return '' !== $this->forced_engine();
	}

	/**
	 * Handle the `?erp_action=switch_ui` URL.
	 *
	 * Validates nonce + capability + page slug + target value, writes user-meta,
	 * then redirects back to the page without the switch params. Aborts silently
	 * on any validation failure (engine stays whatever the resolver picks next).
	 */
	public function handle_switch(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ( $_GET['erp_action'] ?? '' ) !== self::SWITCH_ACTION ) {
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_NAME ) ) {
			return;
		}

		// A site-wide force makes the preference unreachable in resolve_engine(),
		// but the write used to happen anyway: every click during a forced period
		// quietly changed the stored preference, and those changes then took
		// effect the moment an administrator set the option back to `auto`.
		if ( $this->is_engine_forced() ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( ! $this->is_hr_page( $page ) ) {
			return;
		}

		if ( ! current_user_can( 'erp_list_employee' ) ) {
			return;
		}

		$target = isset( $_GET['erp_ui'] ) ? sanitize_key( wp_unslash( $_GET['erp_ui'] ) ) : '';
		if ( ! in_array( $target, [ self::ENGINE_REACT, 'legacy' ], true ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$prefs = (array) get_user_meta( $user_id, self::USERMETA_KEY, true );
		$key   = $this->legacy_key_for_page( $page );

		// Both directions are stored explicitly. An opt-in must survive on sites
		// whose default is legacy — unsetting the key would silently bounce the
		// user back to the old UI on the next request.
		$prefs[ $key ] = ( 'legacy' === $target ) ? 'legacy' : self::ENGINE_REACT;

		update_user_meta( $user_id, self::USERMETA_KEY, $prefs );

		// Land on a URL that differs from the bare `admin.php?page=erp-hr` the
		// browser may have cached for the *other* engine — otherwise it can serve
		// the stale (previous-engine) document and the switch only appears after a
		// manual refresh. `erp_ui` (the now-active engine) makes it cache-distinct
		// and is ignored by the page itself (the resolver reads user-meta).
		$engine_arg = ( 'legacy' === $target ) ? self::ENGINE_LEGACY : self::ENGINE_REACT;

		// Land on the legacy screen that matches where the user actually was.
		// Without this the switch always dropped them on the HR dashboard, which
		// is the whole complaint: leaving Recruitment should arrive at the job
		// openings list. Only applies when switching TO legacy — going the other
		// way, React reads its own route from the hash the browser keeps.
		$redirect = '';

		if ( 'legacy' === $target ) {
			$route = isset( $_GET['erp_route'] ) ? sanitize_text_field( wp_unslash( $_GET['erp_route'] ) ) : '';
			// A hash path only: strip any query string and reject anything that
			// tries to leave it (scheme, host, traversal).
			$route = strtok( $route, '?' );
			$route = ( is_string( $route ) && ! preg_match( '#[:\\\\]|\.\.#', $route ) ) ? $route : '';

			if ( '' !== $route ) {
				$mapped = $this->react_to_legacy_url( $route );

				if ( '' !== $mapped ) {
					$redirect = add_query_arg( 'erp_ui', $engine_arg, $mapped );
				}
			}
		}

		// Switching TO React: carry the legacy section across as a hash route, so
		// the redesign opens on the screen the user just left.
		if ( self::ENGINE_REACT === $target ) {
			$route = $this->legacy_to_react_route(
				isset( $_GET['erp_from_section'] ) ? sanitize_key( wp_unslash( $_GET['erp_from_section'] ) ) : '',
				isset( $_GET['erp_from_sub'] ) ? sanitize_key( wp_unslash( $_GET['erp_from_sub'] ) ) : ''
			);

			if ( '' !== $route ) {
				$redirect = add_query_arg(
					[
						'page'   => $page,
						'erp_ui' => $engine_arg,
					],
					admin_url( 'admin.php' )
				) . '#' . $route;
			}
		}

		if ( '' === $redirect ) {
			$redirect = add_query_arg(
				[
					'page'   => $page,
					'erp_ui' => $engine_arg,
				],
				admin_url( 'admin.php' )
			);
		}

		wp_safe_redirect( $redirect );
		exit;
		// phpcs:enable
	}

	/**
	 * Build a nonce-stamped switch URL for the given page slug and target.
	 *
	 * @param string $page_slug HR admin page slug (must start with `erp-hr`).
	 * @param string $target    Either 'react' or 'legacy'.
	 *
	 * @return string
	 */
	public function switch_url( string $page_slug, string $target ): string {
		$target = in_array( $target, [ self::ENGINE_REACT, 'legacy' ], true ) ? $target : 'legacy';

		// Where the click came FROM, so the redirect can land on the matching
		// screen. Only meaningful legacy → React; the React link appends its own
		// `erp_route` because only the browser knows the current hash.
		$origin = [];

		if ( self::ENGINE_REACT === $target ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$origin['erp_from_section'] = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
			$origin['erp_from_sub']     = isset( $_GET['sub-section'] ) ? sanitize_key( wp_unslash( $_GET['sub-section'] ) ) : '';
			$origin                     = array_filter( $origin );
			// phpcs:enable
		}

		// Build a RAW url (literal `&`). `wp_nonce_url()` HTML-encodes the result
		// (`&` → `&#038;`), which is correct for HTML output but breaks when the
		// URL is JSON-encoded into the boot payload and assigned as a React `href`
		// DOM property (the entities are NOT decoded there, so `$_GET` params get
		// mangled and the switch never fires). Consumers escape at output:
		// AdminMenu wraps this in `esc_url()`; the React side uses it raw.
		$url = add_query_arg(
			array_merge(
				[
					'page'       => $page_slug,
					'erp_action' => self::SWITCH_ACTION,
					'erp_ui'     => $target,
				],
				$origin
			),
			admin_url( 'admin.php' )
		);

		return add_query_arg( '_wpnonce', wp_create_nonce( self::NONCE_NAME ), $url );
	}

	/**
	 * Translate a React hash route (e.g. "/employees/42") into the equivalent
	 * legacy destination for engine handoff.
	 *
	 * Without this, "View legacy version" always landed on the HR dashboard: the
	 * switch URL carried no route, so whichever screen you were on was lost. The
	 * React link now sends `erp_route`, and this turns it into the matching legacy
	 * URL — Recruitment goes to the job-opening list, not the dashboard.
	 *
	 * Only the FIRST path segment is matched (Dokan's panel switcher does the
	 * same): legacy has no equivalent for most of React's deeper routes, and
	 * landing on the right section beats guessing at a sub-screen. `/employees/42`
	 * is the one exception worth carrying, because legacy has that exact view.
	 *
	 * Unmapped routes return '' and the caller falls back to the plain page, which
	 * is the old behaviour — a route this does not know must never produce a
	 * broken URL.
	 *
	 * @param string $hash_path React hash path WITHOUT the leading `#` (query
	 *                          string already stripped).
	 *
	 * @return string Absolute admin URL, or '' when the route has no legacy twin.
	 */
	public function react_to_legacy_url( string $hash_path ): string {
		$hash_path = trim( $hash_path, '/' );
		$segments  = $hash_path === '' ? [] : explode( '/', $hash_path );
		$head      = $segments[0] ?? '';

		// `/employees/42` → the legacy single-employee view. Checked before the
		// section map so the id is not thrown away.
		if ( 'employees' === $head && isset( $segments[1] ) && (int) $segments[1] > 0 ) {
			return $this->hr_section_url(
				[
					'section'     => 'people',
					'sub-section' => 'employee',
					'action'      => 'view',
					'id'          => (int) $segments[1],
				]
			);
		}

		/**
		 * Map of React route head → legacy query args (or an absolute URL string).
		 *
		 * Pro modules register their own screens here rather than patching free —
		 * the same shape as Dokan's `dokan_admin_panel_switch_supported_keys`.
		 *
		 * @since 1.18.0
		 *
		 * @param array  $map       Route head => query args array, or a URL string.
		 * @param string $hash_path The full hash path being translated.
		 */
		$map = apply_filters(
			'erp_hr_react_to_legacy_routes',
			[
				''              => [ 'section' => 'dashboard' ],
				'dashboard'     => [ 'section' => 'dashboard' ],
				'employees'     => [ 'section' => 'people', 'sub-section' => 'employee' ],
				'my-profile'    => [ 'section' => 'my-profile' ],
				'departments'   => [ 'section' => 'people', 'sub-section' => 'employee' ],
				'designations'  => [ 'section' => 'people', 'sub-section' => 'employee' ],
				'announcements' => [ 'section' => 'people', 'sub-section' => 'announcement' ],
				'requests'      => [ 'section' => 'people', 'sub-section' => 'employee' ],
				// Sections whose sub-screens exist one-for-one in legacy keep them;
				// the rest land on the section head, which is still the right place.
				'leave'         => $this->leave_target( $segments[1] ?? '' ),
				'recruitment'   => $this->sub_target(
					'recruitment',
					$segments[1] ?? '',
					[
						''           => 'job-opening',
						'candidates' => 'applicant',
						'reports'    => 'reports',
					]
				),
				'assets'        => $this->sub_target(
					'asset',
					$segments[1] ?? '',
					[
						''           => 'asset',
						'allotments' => 'asset-allottment',
						'requests'   => 'asset-request',
					]
				),
				'payroll'       => $this->sub_target(
					'payroll',
					$segments[1] ?? '',
					[
						''         => 'payrun',
						'payruns'  => 'payrun',
						'settings' => 'calendar',
					]
				),
				'reports'       => $this->report_target( $segments[1] ?? '' ),
				// Pro sections. Harmless when the module is inactive — the legacy
				// page simply reports an unknown section, exactly as a hand-typed
				// URL would, and the link is only shown on screens that exist.
				'attendance'    => [ 'section' => 'attendance' ],
				'documents'     => [ 'section' => 'documents' ],
				'reimbursement' => [ 'section' => 'reimbursement' ],
				// Training is a CPT screen, not an HR section — an absolute URL.
				'training'      => admin_url( 'edit.php?post_type=erp_hr_training' ),
			],
			$hash_path
		);

		if ( ! isset( $map[ $head ] ) ) {
			return '';
		}

		$target = $map[ $head ];

		return is_string( $target ) ? $target : $this->hr_section_url( (array) $target );
	}

	/**
	 * Legacy target for a section whose sub-screens map one-for-one.
	 *
	 * @param string   $section Legacy section slug.
	 * @param string   $sub     Second React path segment.
	 * @param string[] $map     React segment => legacy sub-section ('' = default).
	 *
	 * @return array Query args.
	 */
	private function sub_target( string $section, string $sub, array $map ): array {
		$legacy_sub = $map[ $sub ] ?? ( $map[''] ?? '' );
		$args       = [ 'section' => $section ];

		if ( '' !== $legacy_sub ) {
			$args['sub-section'] = $legacy_sub;
		}

		return $args;
	}

	/**
	 * Legacy target for a `/reports/...` route.
	 *
	 * Legacy keys each report off `type=` rather than a sub-section, and the
	 * slugs match React's one-for-one.
	 *
	 * @param string $sub Second React path segment.
	 *
	 * @return array Query args.
	 */
	private function report_target( string $sub ): array {
		$types = [ 'age-profile', 'gender-profile', 'headcount', 'leaves', 'salary-history', 'years-of-service' ];
		$args  = [ 'section' => 'report' ];

		if ( in_array( $sub, $types, true ) ) {
			$args['type'] = $sub;
		}

		return $args;
	}

	/**
	 * Legacy target for a `/leave/...` route.
	 *
	 * @param string $sub Second path segment ('requests', 'policies', …).
	 *
	 * @return array Query args for the HR page.
	 */
	private function leave_target( string $sub ): array {
		$subs = [
			'requests'     => 'leave-requests',
			'entitlements' => 'leave-entitlements',
			'calendar'     => 'leave-calendar',
			'policies'     => 'policies',
			'holidays'     => 'holidays',
		];

		$args = [ 'section' => 'leave' ];

		if ( isset( $subs[ $sub ] ) ) {
			$args['sub-section'] = $subs[ $sub ];
		}

		return $args;
	}

	/**
	 * Absolute URL for an HR admin page with the given query args.
	 *
	 * @param array $args Query args (section, sub-section, …).
	 *
	 * @return string
	 */
	private function hr_section_url( array $args ): string {
		return add_query_arg(
			array_merge( [ 'page' => 'erp-hr' ], $args ),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Translate a legacy section/sub-section into the equivalent React hash route.
	 *
	 * The mirror of react_to_legacy_url(): leaving the legacy Recruitment list for
	 * the redesign should arrive at `#/recruitment`, not the dashboard. Reads the
	 * CURRENT request's `section` / `sub-section`, which is what the legacy page
	 * itself is rendering from.
	 *
	 * @param string $section     Legacy `section` query arg.
	 * @param string $sub_section Legacy `sub-section` query arg.
	 *
	 * @return string Hash route beginning with `/`, or '' when there is no twin.
	 */
	public function legacy_to_react_route( string $section, string $sub_section ): string {
		$section     = sanitize_key( $section );
		$sub_section = sanitize_key( $sub_section );

		$leave_subs = [
			'leave-requests'     => '/leave/requests',
			'leave-entitlements' => '/leave/entitlements',
			'leave-calendar'     => '/leave/calendar',
			'policies'           => '/leave/policies',
			'holidays'           => '/leave/holidays',
		];

		if ( 'leave' === $section ) {
			return $leave_subs[ $sub_section ] ?? '/leave/requests';
		}

		if ( 'people' === $section ) {
			return 'announcement' === $sub_section ? '/announcements' : '/employees';
		}

		if ( 'recruitment' === $section ) {
			$subs = [ 'applicant' => '/recruitment/candidates', 'reports' => '/recruitment/reports' ];

			return $subs[ $sub_section ] ?? '/recruitment';
		}

		if ( 'asset' === $section ) {
			$subs = [ 'asset-allottment' => '/assets/allotments', 'asset-request' => '/assets/requests' ];

			return $subs[ $sub_section ] ?? '/assets';
		}

		if ( 'payroll' === $section ) {
			return 'calendar' === $sub_section ? '/payroll/settings' : '/payroll';
		}

		if ( 'report' === $section ) {
			// Legacy keys reports off `type=`, not a sub-section.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$type  = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
			$types = [ 'age-profile', 'gender-profile', 'headcount', 'leaves', 'salary-history', 'years-of-service' ];

			return in_array( $type, $types, true ) ? '/reports/' . $type : '/reports';
		}

		/**
		 * Map of legacy section → React hash route.
		 *
		 * Pro modules add their own screens here instead of patching free.
		 *
		 * @since 1.18.0
		 *
		 * @param array  $map         Section => hash route.
		 * @param string $sub_section The sub-section being translated.
		 */
		$map = apply_filters(
			'erp_hr_legacy_to_react_routes',
			[
				'dashboard'     => '/',
				'my-profile'    => '/my-profile',
				'report'        => '/reports',
				'attendance'    => '/attendance',
				'asset'         => '/assets',
				'documents'     => '/documents',
				'payroll'       => '/payroll',
				'reimbursement' => '/reimbursement',
				'recruitment'   => '/recruitment',
			],
			$sub_section
		);

		return $map[ $section ] ?? '';
	}

	private function is_hr_page( string $page ): bool {
		return $page !== '' && strpos( $page, 'erp-hr' ) === 0;
	}

	/**
	 * Per-page key under the user-meta preference array.
	 *
	 * Free HR has only one admin slug (`erp-hr`); the key is derived for
	 * forward-compatibility when future HR slugs are added.
	 */
	private function legacy_key_for_page( string $page_slug ): string {
		// Only HR's own slugs carry a per-page key. Anything else — a pro module
		// asking about its own page, say — maps to `dashboard`, the key the HR
		// switch actually writes. Deriving a key from an unrecognised slug
		// produced one that can never exist in the preference array, so the
		// lookup missed, the install default (react) applied, and a user who had
		// explicitly opted out of the redesign got it anyway on those pages.
		if ( ! $this->is_hr_page( $page_slug ) ) {
			return 'dashboard';
		}

		$key = preg_replace( '/^erp-hr-?/', '', $page_slug );
		return ( is_string( $key ) && $key !== '' ) ? $key : 'dashboard';
	}
}
