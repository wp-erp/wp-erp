<?php
/**
 * WP-ERP HR — React shell enqueue helper.
 *
 * Loads the per-page React bundle emitted by
 * `@wordpress/dependency-extraction-webpack-plugin` (one `<entry>.js` plus a
 * matching `<entry>.asset.php` containing `dependencies` + `version`).
 *
 * Reads the `__ERP_HR_BOOT__` boot payload required by the React shell:
 * identity, REST root, nonce, switch URL, capability preload, locale, RTL,
 * theme preference, and filter-name mirror.
 *
 * Called from `HRM.php::admin_scripts()` after the UiEngineResolver returns
 * `'react'` — the legacy enqueue path stays byte-for-byte intact.
 */

namespace WeDevs\ERP\HRM\Admin;

defined( 'ABSPATH' ) || exit;

final class Enqueue {

	/**
	 * Map of `page_slug` → React bundle entry name (matches webpack entries).
	 *
	 * For the first deliverable only the Employee List ships, so the single
	 * admin slug `erp-hr` maps to the `employees` entry. Future pages add
	 * one row each.
	 */
	private const PAGE_ENTRIES = [
		'erp-hr' => 'employees',
	];

	/**
	 * Stable script handle for the React bundle on a given page.
	 *
	 * @param string $entry Bundle entry name.
	 *
	 * @return string
	 */
	private static function handle( string $entry ): string {
		return 'erp-hr-react-' . sanitize_key( $entry );
	}

	/**
	 * Register the shared `vendor.js` split-chunk handle (idempotent).
	 *
	 * Webpack splitChunks emits `vendor.js` carrying React-bound libraries
	 * shared across page entries. Both vendor and entry are initial chunks,
	 * so vendor must be enqueued first and the entry script declared
	 * dependent on it.
	 *
	 * Returns the handle name on success, empty string when the artifact is
	 * missing (callers should still enqueue the entry; webpack runtime will
	 * fall back to inline modules where possible).
	 */
	private static function register_vendor_chunk( string $fallback_version ): string {
		$asset_path = WPERP_HRM_PATH . '/assets/dist-react/vendor.asset.php';
		if ( ! file_exists( $asset_path ) ) {
			return '';
		}

		$asset = include $asset_path;
		if ( ! is_array( $asset ) ) {
			$asset = [];
		}
		$deps    = isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] )
			? array_values( array_map( 'strval', $asset['dependencies'] ) )
			: [];
		$version = isset( $asset['version'] ) ? (string) $asset['version'] : $fallback_version;

		$handle = 'erp-hr-react-vendor';

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script(
				$handle,
				WPERP_HRM_ASSETS . '/dist-react/vendor.js',
				$deps,
				$version,
				true
			);
		}

		return $handle;
	}

	/**
	 * Enqueue the React bundle for the given HR admin page.
	 *
	 * @param string $page_slug HR admin page slug (e.g. `erp-hr`).
	 *
	 * @return bool True when a bundle was enqueued; false when the page is not
	 *              wired or the build artifact is missing.
	 */
	public static function for_page( string $page_slug ): bool {
		$entry = self::PAGE_ENTRIES[ $page_slug ] ?? null;
		if ( ! $entry ) {
			return false;
		}

		$asset_path = WPERP_HRM_PATH . '/assets/dist-react/' . $entry . '.asset.php';
		if ( ! file_exists( $asset_path ) ) {
			self::warn_missing_build( $page_slug, $entry, $asset_path );
			return false;
		}

		$asset = include $asset_path;
		if ( ! is_array( $asset ) ) {
			$asset = [];
		}
		$deps    = isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] )
			? array_values( array_map( 'strval', $asset['dependencies'] ) )
			: [];
		$version = isset( $asset['version'] ) ? (string) $asset['version'] : (string) WPERP_HRM_VERSION;

		// Vendor split-chunk emitted by webpack `splitChunks` (name: 'vendor').
		// Must load BEFORE the entry chunk because both are initial chunks
		// (not lazy). Entry script is then declared dependent on the vendor
		// handle so wp_enqueue_script orders them correctly.
		$vendor_handle = self::register_vendor_chunk( $version );
		if ( $vendor_handle ) {
			$deps[] = $vendor_handle;
		}

		$handle = self::handle( $entry );

		wp_register_script(
			$handle,
			WPERP_HRM_ASSETS . '/dist-react/' . $entry . '.js',
			$deps,
			$version,
			true
		);

		// Boot payload — emitted as `window.__ERP_HR_BOOT__ = {...};` BEFORE
		// the bundle runs.
		$payload = self::build_boot_payload( $page_slug );
		wp_add_inline_script(
			$handle,
			'window.__ERP_HR_BOOT__ = ' . wp_json_encode( $payload ) . ';',
			'before'
		);

		wp_enqueue_script( $handle );

		if ( function_exists( 'wp_set_script_translations' ) ) {
			$lang_dir = defined( 'WPERP_PATH' ) ? WPERP_PATH . '/i18n/languages' : '';
			if ( $lang_dir ) {
				wp_set_script_translations( $handle, 'erp', $lang_dir );
			}
		}

		// CSS — emitted as `<entry>.css` when the entry imports CSS. wp-scripts
		// also emits `<entry>-rtl.css`; `wp_style_add_data( …, 'rtl', 'replace' )`
		// makes WordPress swap to it automatically on RTL locales.
		$css_relative = '/assets/dist-react/' . $entry . '.css';
		if ( file_exists( WPERP_HRM_PATH . $css_relative ) ) {
			wp_register_style(
				$handle,
				WPERP_HRM_ASSETS . '/dist-react/' . $entry . '.css',
				[],
				$version
			);
			wp_style_add_data( $handle, 'rtl', 'replace' );
			wp_enqueue_style( $handle );
		}

		// Collapse the WP admin sidebar to its native folded (icon-strip) state
		// on HR React pages — done via `admin_body_class` so WP's own CSS
		// handles the layout transition. The admin bar stays at its native
		// 32 px; our top bar renders BELOW it inside `#wpcontent`.
		add_filter( 'admin_body_class', [ self::class, 'admin_body_class' ] );
		add_action( 'admin_head', [ self::class, 'print_admin_chrome_overrides' ] );

		// Suppress WP core's "Screen Options" tab on the React shell. It is a
		// leftover from the legacy list-table screen (per-page count, column
		// toggles) and has no meaning in the SPA — it only overlaps the custom
		// top bar. Registered here (during enqueue) so it runs before WP renders
		// the screen-meta tab in the admin header.
		add_filter( 'screen_options_show_screen', '__return_false' );

		return true;
	}

	/**
	 * Force the WP admin sidebar into its native folded (icon-strip) state on
	 * HR React pages. WP core ships full CSS for `.folded` — adding the class
	 * triggers their built-in collapse without us hiding the menu.
	 *
	 * @param string $classes Space-separated body class string.
	 *
	 * @return string
	 */
	public static function admin_body_class( string $classes ): string {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || strpos( (string) $screen->id, 'erp-hr' ) === false ) {
			return $classes;
		}
		if ( false !== strpos( ' ' . $classes . ' ', ' folded ' ) ) {
			return $classes;
		}
		return trim( $classes . ' folded erp-hr-react-page' );
	}

	/**
	 * Build the `__ERP_HR_BOOT__` payload localized to `window`.
	 *
	 * @param string $page_slug Current admin page slug.
	 *
	 * @return array
	 */
	private static function build_boot_payload( string $page_slug ): array {
		$user_id = (int) get_current_user_id();
		$user    = wp_get_current_user();

		$capabilities = [];
		$caps_keys    = self::hr_capability_keys();
		foreach ( $caps_keys as $cap ) {
			$capabilities[ $cap ] = current_user_can( $cap );
		}

		$theme_mode = self::resolve_theme_mode( $user_id );
		$color_scheme = self::resolve_color_scheme( $theme_mode );
		$nav_layout = self::resolve_nav_layout( $user_id );

		$payload = [
			'currentUserId' => $user_id,
			'displayName'   => (string) ( $user->display_name ?? '' ),
			'email'         => (string) ( $user->user_email ?? '' ),
			'avatarUrl'     => get_avatar_url( $user_id, [ 'size' => 80 ] ) ?: '',
			'isPro'         => class_exists( 'WP_ERP_Pro' ),
			'isHrManager'   => function_exists( 'erp_hr_get_manager_role' )
				? in_array( erp_hr_get_manager_role(), (array) $user->roles, true )
				: false,
			'api'           => [
				'nsV1' => 'erp/v1',
				'nsV2' => 'erp/v2',
				'root' => esc_url_raw( get_rest_url() ),
			],
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'locale'        => function_exists( 'determine_locale' ) ? determine_locale() : get_locale(),
			'isRTL'         => is_rtl(),
			'colorScheme'   => $color_scheme,
			'themeMode'     => $theme_mode,
			'navLayout'     => $nav_layout,
			'switchUrl'     => UiEngineResolver::instance()->switch_url( $page_slug, 'legacy' ),
			'pageSlug'      => $page_slug,
			'assets'        => [
				'logoUrl'      => WPERP_HRM_ASSETS . '/images/logo.svg',
				// Base URL for the shared pro-popup illustration set, reused by the
				// React "Upgrade to Pro" upsell modal (parity with the legacy popup).
				'proPopupUrl'  => defined( 'WPERP_ASSETS' ) ? WPERP_ASSETS . '/images/pro-popup' : '',
			],
			'capabilities'  => $capabilities,
			'hrmVersion'    => defined( 'WPERP_HRM_VERSION' ) ? (string) WPERP_HRM_VERSION : '',
			// Country / state lookups for the employee-form address selects
			// (parity with the legacy new-employee.php Countries dropdowns).
			'countries'     => self::build_countries(),
			'states'        => self::build_states(),
			// Active pro HR sub-modules — each self-registers via the
			// `erp_hr_v2_boot_payload` filter. Drives module-gated nav items.
			'modules'       => [],
			'filters'       => [
				'topbarRightItems' => 'erp_hr.topbar.right_items',
				'userMenuItems'    => 'erp_hr.user_menu.items',
				'routes'           => 'erp_hr.routes',
				'capsChanged'      => 'erp_hr.caps.changed',
				'themeChanged'     => 'erp_hr.theme.changed',
				'shellReady'       => 'erp_hr.shell.ready',
			],
		];

		/**
		 * Filter the React shell boot payload.
		 *
		 * Pro plugins append new top-level keys (e.g. license info, pro-only
		 * preload data). Pro must never replace a free key.
		 *
		 * @since 1.13.5
		 *
		 * @param array  $payload   Boot payload.
		 * @param string $page_slug Current page slug.
		 */
		return (array) apply_filters( 'erp_hr_v2_boot_payload', $payload, $page_slug );
	}

	/**
	 * Country list for the employee-form selects.
	 *
	 * Returns `[ { value: <code>, label: <name> }, … ]`, sourced from the same
	 * `\WeDevs\ERP\Countries` data the legacy new-employee.php form used.
	 *
	 * @return array
	 */
	private static function build_countries(): array {
		if ( ! class_exists( '\WeDevs\ERP\Countries' ) ) {
			return [];
		}

		$countries = \WeDevs\ERP\Countries::instance()->get_countries();
		$options   = [];

		foreach ( (array) $countries as $code => $name ) {
			$options[] = [
				'value' => (string) $code,
				'label' => (string) $name,
			];
		}

		return $options;
	}

	/**
	 * State lookup keyed by country code, for the country-dependent state
	 * select. Only countries that actually define states are included.
	 *
	 * Returns `{ <countryCode>: [ { value: <stateCode>, label: <name> }, … ] }`.
	 *
	 * @return array
	 */
	private static function build_states(): array {
		if ( ! class_exists( '\WeDevs\ERP\Countries' ) ) {
			return [];
		}

		$all   = \WeDevs\ERP\Countries::instance()->get_states();
		$byCode = [];

		foreach ( (array) $all as $code => $states ) {
			if ( empty( $states ) || ! is_array( $states ) ) {
				continue;
			}

			$options = [];
			foreach ( $states as $state_code => $state_name ) {
				$options[] = [
					'value' => (string) $state_code,
					'label' => (string) $state_name,
				];
			}

			$byCode[ (string) $code ] = $options;
		}

		return $byCode;
	}

	/**
	 * Resolve the user's preferred theme mode.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return string 'light' | 'dark' | 'auto'
	 */
	private static function resolve_theme_mode( int $user_id ): string {
		if ( ! $user_id ) {
			return 'auto';
		}
		$stored = (string) get_user_meta( $user_id, 'erp_hr_color_scheme', true );
		return in_array( $stored, [ 'light', 'dark', 'auto' ], true ) ? $stored : 'auto';
	}

	/**
	 * Resolve the user's preferred nav layout for first paint.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return string 'topbar' | 'sidebar'
	 */
	private static function resolve_nav_layout( int $user_id ): string {
		if ( ! $user_id ) {
			return 'topbar';
		}
		$stored = (string) get_user_meta( $user_id, 'erp_hr_nav_layout', true );
		return in_array( $stored, [ 'topbar', 'sidebar' ], true ) ? $stored : 'topbar';
	}

	/**
	 * Resolve the concrete color scheme PHP should hint to the React shell.
	 *
	 * Resolution order: explicit user mode → WP admin color scheme → 'light'.
	 *
	 * @param string $mode 'light' | 'dark' | 'auto'.
	 *
	 * @return string 'light' | 'dark'
	 */
	private static function resolve_color_scheme( string $mode ): string {
		if ( 'dark' === $mode ) {
			return 'dark';
		}
		if ( 'light' === $mode ) {
			return 'light';
		}

		$wp_scheme = (string) get_user_option( 'admin_color' );
		if ( in_array( $wp_scheme, [ 'midnight', 'coffee', 'ectoplasm' ], true ) ) {
			return 'dark';
		}
		return 'light';
	}

	/**
	 * Union of every HR capability key.
	 *
	 * @return string[]
	 */
	private static function hr_capability_keys(): array {
		if ( ! function_exists( 'erp_hr_get_caps_for_role' ) || ! function_exists( 'erp_hr_get_manager_role' ) ) {
			return [];
		}
		$manager  = (array) erp_hr_get_caps_for_role( erp_hr_get_manager_role() );
		$employee = function_exists( 'erp_hr_get_employee_role' )
			? (array) erp_hr_get_caps_for_role( erp_hr_get_employee_role() )
			: [];
		$keys = array_keys( array_merge( $manager, $employee ) );

		// The HR-manager role doubles as the gate for the Reports menu
		// (AdminMenu.php uses `'capability' => 'erp_hr_manager'`). It is a role,
		// not one of the per-role cap keys, so add it explicitly — otherwise the
		// React Reports nav can never resolve its gate from the boot payload.
		// Mirrors MeControllerV2::hr_capability_keys().
		$keys[] = erp_hr_get_manager_role();

		// Legacy-menu gates the React nav follows but which are NOT per-role HR cap
		// keys: Recruitment menu gates on `manage_recruitment`, Reimbursement on the
		// `employee` cap. Add them so the nav can resolve those gates from the boot
		// payload (mirrors MeControllerV2::hr_capability_keys()).
		$keys[] = 'manage_recruitment';
		$keys[] = 'employee';

		return array_values( array_unique( array_map( 'strval', $keys ) ) );
	}

	/**
	 * Print minimal CSS that lets the React shell render edge-to-edge inside
	 * `#wpcontent` while keeping WP's native admin bar (32 px) and the folded
	 * admin sidebar (36 px icon strip) intact.
	 *
	 * What this DOES:
	 *   - Strips `#wpcontent`'s default 20 px left padding on HR React pages,
	 *     so our app rail sits flush against the folded sidebar.
	 *   - Hides `.wrap > h1.wp-heading-inline` if any plugin still injects it
	 *     above our mount node.
	 *   - Resets `#wpbody-content`'s `.notice` reservation padding so our
	 *     full-bleed top bar can render below the WP admin bar.
	 *
	 * What this DOES NOT do:
	 *   - Shrink, recolor, or hide the WP admin bar (#wpadminbar).
	 *   - Hide the admin sidebar — `admin_body_class` adds `folded` so WP's
	 *     own CSS collapses it to its icon-strip state.
	 *   - Touch any WP global CSS that other plugins rely on.
	 */
	public static function print_admin_chrome_overrides(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || strpos( (string) $screen->id, 'erp-hr' ) === false ) {
			return;
		}
		?>
		<style id="erp-hr-react-admin-chrome">
			/* Strip WP's default content padding so the React app sits flush. */
			body.erp-hr-react-page #wpbody-content { padding-bottom: 0; }
			body.erp-hr-react-page #wpcontent { padding-left: 0; }
			body.erp-hr-react-page #wpfooter { display: none; }

			/* Hide any leftover `.wrap > h1` core might inject above mount. */
			body.erp-hr-react-page #wpbody-content > .wrap > h1,
			body.erp-hr-react-page #wpbody-content > .wrap > h2 { display: none; }

			/* Mount fills available height below the admin bar + content gutter. */
			body.erp-hr-react-page #erp-hr-app { min-height: calc( 100vh - 32px ); }
			@media screen and ( max-width: 782px ) {
				body.erp-hr-react-page #erp-hr-app { min-height: calc( 100vh - 46px ); }
			}
		</style>
		<?php
	}

	/**
	 * Emit a developer-facing admin notice when the wp-scripts build artifact
	 * is missing. Visible only when `WP_DEBUG` is on.
	 */
	private static function warn_missing_build( string $page_slug, string $entry, string $expected_path ): void {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			return;
		}

		add_action(
			'admin_notices',
			static function () use ( $page_slug, $entry, $expected_path ) {
				printf(
					'<div class="notice notice-error"><p><strong>WP-ERP HR (dev):</strong> React bundle for <code>%s</code> not found. Expected <code>%s</code>. Run <code>cd modules/hrm && nvm use && npm run build</code>. Falling back to legacy engine.</p></div>',
					esc_html( $page_slug . ' / ' . $entry ),
					esc_html( $expected_path )
				);
			}
		);
	}
}
