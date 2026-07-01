<?php
/**
 * WP-ERP HR — UI engine resolver.
 *
 * Decides whether each redesigned HR admin page is served by the new React
 * engine or the legacy Vue/jQuery engine. Default = React. Users can opt into
 * legacy per page via a nonce-verified switch action; the preference is stored
 * in user-meta. Operators can force a site-wide engine via the `erp_hr_ui_engine`
 * site option.
 *
 * Contract verified at openspec/changes/redesign-hr-free/ui-coexistence.md
 * (Resolution flow, Server-side wiring).
 */

namespace WeDevs\ERP\HRM\Admin;

defined( 'ABSPATH' ) || exit;

final class UiEngineResolver {

	public const SWITCH_ACTION = 'switch_ui';
	public const NONCE_NAME    = 'erp_switch_ui';
	public const USERMETA_KEY  = 'erp_hr_ui_pref';
	public const SITE_OPTION   = 'erp_hr_ui_engine';

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
	 * Resolve which engine should render the given HR admin page slug.
	 *
	 * Resolution order (highest priority first):
	 *   1. Site option `erp_hr_ui_engine` if forced to 'react' or 'vue'.
	 *   2. User-meta `erp_hr_ui_pref[$key] === 'legacy'` → return 'vue'.
	 *   3. Default → return 'react'.
	 *
	 * The URL switch action is handled separately in handle_switch() and
	 * performs a redirect; it does not return here.
	 */
	public function resolve_engine( string $page_slug ): string {
		$forced = (string) get_option( self::SITE_OPTION, 'auto' );
		if ( in_array( $forced, [ self::ENGINE_REACT, self::ENGINE_LEGACY ], true ) ) {
			return $forced;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return self::ENGINE_REACT;
		}

		$prefs = (array) get_user_meta( $user_id, self::USERMETA_KEY, true );
		$key   = $this->legacy_key_for_page( $page_slug );

		return ( ( $prefs[ $key ] ?? '' ) === 'legacy' )
			? self::ENGINE_LEGACY
			: self::ENGINE_REACT;
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

		if ( 'legacy' === $target ) {
			$prefs[ $key ] = 'legacy';
		} else {
			unset( $prefs[ $key ] );
		}

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
		$key = preg_replace( '/^erp-hr-?/', '', $page_slug );
		return ( is_string( $key ) && $key !== '' ) ? $key : 'dashboard';
	}
}
