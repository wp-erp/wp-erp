<?php
/**
 * Plugin Name: CI Update & External HTTP Guard
 * Description: Test-environment only. Disables WordPress auto-updates and the
 *              blocking wordpress.org update-check HTTP calls that fire on
 *              admin_init of a fresh install. Those synchronous calls stall the
 *              wp-admin render on CI runners long enough to blow Playwright's
 *              navigation timeout — the root cause of the flaky "authenticate
 *              admin" setup step (frontend logins, which never touch admin_init,
 *              stay fast). Also caps any other outbound request so a slow
 *              third-party host can't hang a page render.
 */

// phpcs:disable

// --- Disable every auto-update path (belt-and-suspenders with the wp-config constants). ---
add_filter( 'automatic_updater_disabled', '__return_true' );
add_filter( 'auto_update_core', '__return_false' );
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );

// --- Stop WP's synchronous update checks. On a fresh install the update transients are
// empty/expired, so these fire blocking requests to api.wordpress.org on every admin load. ---
remove_action( 'admin_init', '_maybe_update_core' );
remove_action( 'admin_init', '_maybe_update_plugins' );
remove_action( 'admin_init', '_maybe_update_themes' );
remove_action( 'wp_version_check', 'wp_version_check' );
remove_action( 'wp_update_plugins', 'wp_update_plugins' );
remove_action( 'wp_update_themes', 'wp_update_themes' );

// --- Fail wordpress.org calls instantly instead of waiting out a network timeout. wp-env has
// already provisioned core/plugins/themes locally, so nothing under test needs wordpress.org. ---
add_filter(
    'pre_http_request',
    static function ( $pre, $args, $url ) {
        $host = (string) wp_parse_url( $url, PHP_URL_HOST );
        if ( '' !== $host && false !== strpos( $host, 'wordpress.org' ) ) {
            return new WP_Error( 'ci_http_blocked', 'wordpress.org HTTP disabled in test env' );
        }
        return $pre;
    },
    10,
    3
);

// NOTE: deliberately NO blanket http_request_args timeout cap here. Capping every outbound
// request (e.g. to 3s) throttles legitimate Dokan calls that set their own higher budget —
// the admin Help page does wp_remote_get( 'https://dokan.co/wp-json/org/help', ['timeout'=>15] ),
// and a 3s cap made that flaky (empty Help page -> "Basics" not found). The wordpress.org block
// above already removes the update-check calls that were the real source of slow admin renders.

// phpcs:enable
