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
		$redirect = add_query_arg(
			[
				'page'   => $page,
				'erp_ui' => ( 'legacy' === $target ) ? self::ENGINE_LEGACY : self::ENGINE_REACT,
			],
			admin_url( 'admin.php' )
		);

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

		// Build a RAW url (literal `&`). `wp_nonce_url()` HTML-encodes the result
		// (`&` → `&#038;`), which is correct for HTML output but breaks when the
		// URL is JSON-encoded into the boot payload and assigned as a React `href`
		// DOM property (the entities are NOT decoded there, so `$_GET` params get
		// mangled and the switch never fires). Consumers escape at output:
		// AdminMenu wraps this in `esc_url()`; the React side uses it raw.
		$url = add_query_arg(
			[
				'page'       => $page_slug,
				'erp_action' => self::SWITCH_ACTION,
				'erp_ui'     => $target,
			],
			admin_url( 'admin.php' )
		);

		return add_query_arg( '_wpnonce', wp_create_nonce( self::NONCE_NAME ), $url );
	}

	/**
	 * Translate a React hash route (e.g. "/employees/42") into the equivalent
	 * legacy section/sub-section query string for engine handoff.
	 *
	 * Currently only `/employees` is wired (first deliverable). Future routes
	 * extend this map.
	 *
	 * @param string $hash_path React hash path WITHOUT the leading `#`.
	 *
	 * @return string Query string fragment beginning with `&` (or empty string).
	 */
	public function react_to_legacy_url( string $hash_path ): string {
		$hash_path = trim( $hash_path, '/' );

		if ( '' === $hash_path || 'employees' === $hash_path ) {
			return '&section=people&sub-section=employee';
		}

		if ( 0 === strpos( $hash_path, 'employees/' ) ) {
			$id = (int) substr( $hash_path, strlen( 'employees/' ) );
			if ( $id > 0 ) {
				return '&section=people&sub-section=employee&action=view&id=' . $id;
			}
			return '&section=people&sub-section=employee';
		}

		return '';
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
