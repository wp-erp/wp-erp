<?php
/**
 * Plugin Name: WP ERP test helpers
 * Description: Test-environment only. Exposes the few pieces of ERP state the
 *              Playwright suite cannot read through the UI or the REST API,
 *              behind a shared-secret guard. Never ships to a real site.
 */

// phpcs:disable

/**
 * All endpoints require ?erp_test_key=<ERP_TEST_KEY>. The key is set from
 * wp-config (see .wp-env.json config) and defaults to a fixed local value.
 */
function erp_pw_test_key() {
    return defined( 'ERP_TEST_KEY' ) ? ERP_TEST_KEY : 'erp-pw-local';
}

function erp_pw_authorized() {
    $key = isset( $_REQUEST['erp_test_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['erp_test_key'] ) ) : '';

    return hash_equals( (string) erp_pw_test_key(), (string) $key );
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'erp-pw/v1', '/license', [
        'methods'             => 'GET',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function () {
            // The raw option, so the suite can assert the real server response
            // (users, tier, license_limit, activations_left) rather than a
            // re-rendered version of it.
            return [
                'license'        => get_option( 'erp_pro_license' ),
                'license_status' => get_option( 'erp_pro_license_status' ),
            ];
        },
    ] );

    register_rest_route( 'erp-pw/v1', '/user-count', [
        'methods'             => 'GET',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function () {
            // Mirrors erp-pro Update::count_users() so the 100/101-user test
            // asserts the number the product itself enforces on, not a guess.
            if ( ! function_exists( 'wp_erp_pro' ) || ! isset( wp_erp_pro()->update ) ) {
                return new WP_Error( 'erp_pro_missing', 'erp-pro is not active', [ 'status' => 500 ] );
            }

            $update = wp_erp_pro()->update;

            return [
                'counted_roles' => $update->get_counted_roles(),
                'count_users'   => $update->count_users(),
                'licensed_user' => $update->get_licensed_user(),
            ];
        },
    ] );

    register_rest_route( 'erp-pw/v1', '/cron', [
        'methods'             => 'POST',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            $hook = $request->get_param( 'hook' );

            if ( empty( $hook ) ) {
                return new WP_Error( 'missing_hook', 'hook is required', [ 'status' => 400 ] );
            }

            do_action( $hook );

            return [ 'fired' => $hook ];
        },
    ] );

    register_rest_route( 'erp-pw/v1', '/notices', [
        'methods'             => 'GET',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            // erp-pro stores the reverted-role warning in a per-user transient
            // that is deleted the first time it renders; the suite reads it here
            // when the assertion cannot wait for an admin page load.
            $user_id = (int) $request->get_param( 'user_id' );
            $key     = 'erp_pro_user_limit_role_notice_' . $user_id;

            return [ 'notice' => get_transient( $key ) ];
        },
    ] );


    /**
     * Create a user with an arbitrary role.
     *
     * WP core's /wp/v2/users refuses ERP roles with rest_user_invalid_role
     * because they are not in get_editable_roles(); wp_insert_user has no such
     * restriction. Seeding only — never a product path.
     */
    register_rest_route( 'erp-pw/v1', '/users', [
        'methods'             => 'POST',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            $login = sanitize_user( (string) $request->get_param( 'login' ), true );
            $email = sanitize_email( (string) $request->get_param( 'email' ) );
            $pass  = (string) $request->get_param( 'password' );
            $role  = sanitize_key( (string) $request->get_param( 'role' ) );

            if ( '' === $login || '' === $email || '' === $pass ) {
                return new WP_Error( 'missing_field', 'login, email and password are required', [ 'status' => 400 ] );
            }

            $existing = get_user_by( 'login', $login );

            if ( $existing ) {
                if ( $role && ! in_array( $role, (array) $existing->roles, true ) ) {
                    $existing->set_role( $role );
                }

                // A user left behind by an earlier run carries an unknown
                // password; resetting it makes seeding idempotent instead of
                // failing at the login step three tests later.
                wp_set_password( $pass, $existing->ID );

                return [ 'id' => $existing->ID, 'created' => false, 'roles' => array_values( (array) get_user_by( 'id', $existing->ID )->roles ) ];
            }

            $user_id = wp_insert_user( [
                'user_login' => $login,
                'user_email' => $email,
                'user_pass'  => $pass,
                'role'       => $role ? $role : 'subscriber',
            ] );

            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }

            return [ 'id' => $user_id, 'created' => true, 'roles' => array_values( (array) get_user_by( 'id', $user_id )->roles ) ];
        },
    ] );

    /**
     * Bulk-create N users in one role. The 100/101-user licence test needs 99 of
     * them; doing that one HTTP call at a time takes minutes.
     */
    register_rest_route( 'erp-pw/v1', '/users/bulk', [
        'methods'             => 'POST',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            $prefix = sanitize_user( (string) $request->get_param( 'prefix' ), true );
            $role   = sanitize_key( (string) $request->get_param( 'role' ) );
            $count  = (int) $request->get_param( 'count' );
            $pass   = (string) $request->get_param( 'password' );

            if ( '' === $prefix || $count < 1 || $count > 500 ) {
                return new WP_Error( 'bad_request', 'prefix required; count must be 1-500', [ 'status' => 400 ] );
            }

            $created = [];

            for ( $i = 1; $i <= $count; $i++ ) {
                $login = $prefix . $i;

                if ( get_user_by( 'login', $login ) ) {
                    continue;
                }

                $user_id = wp_insert_user( [
                    'user_login' => $login,
                    'user_email' => $login . '@example.test',
                    'user_pass'  => $pass ? $pass : wp_generate_password( 16 ),
                    'role'       => $role ? $role : 'subscriber',
                ] );

                if ( ! is_wp_error( $user_id ) ) {
                    $created[] = $user_id;
                }
            }

            wp_cache_delete( 'erp-pro-get-employees-count', 'erp' );

            return [ 'created' => count( $created ), 'ids' => $created ];
        },
    ] );

    /** Deletes every user whose login starts with the given prefix. */
    register_rest_route( 'erp-pw/v1', '/users/cleanup', [
        'methods'             => 'POST',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            global $wpdb;

            $prefix = sanitize_user( (string) $request->get_param( 'prefix' ), true );

            if ( '' === $prefix ) {
                return new WP_Error( 'bad_request', 'prefix is required', [ 'status' => 400 ] );
            }

            require_once ABSPATH . 'wp-admin/includes/user.php';

            $ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_login LIKE %s", $wpdb->esc_like( $prefix ) . '%' ) );

            $deleted = 0;

            foreach ( $ids as $id ) {
                // Drop the ERP employee row first so the HR tables do not keep
                // an orphan pointing at a user that no longer exists.
                $wpdb->delete( $wpdb->prefix . 'erp_hr_employees', [ 'user_id' => (int) $id ] );

                if ( wp_delete_user( (int) $id ) ) {
                    $deleted++;
                }
            }

            wp_cache_delete( 'erp-pro-get-employees-count', 'erp' );

            return [ 'deleted' => $deleted ];
        },
    ] );


    /**
     * Set allow-listed options. Used by setup to put WooCommerce into a real,
     * shop-open state (Site Visibility = Live, store address, onboarding done)
     * without clicking through the onboarding wizard on every run.
     *
     * The allow-list is deliberate: this endpoint must never become a way to
     * write arbitrary WordPress options.
     */
    register_rest_route( 'erp-pw/v1', '/options', [
        'methods'             => 'POST',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            $allowed_prefixes = [ 'woocommerce_', 'erp_', 'wp-erp_', 'blog', 'timezone_string', 'date_format', 'time_format', 'start_of_week', 'permalink_structure' ];
            $options          = (array) $request->get_param( 'options' );
            $written          = [];
            $refused          = [];

            foreach ( $options as $name => $value ) {
                $ok = false;

                foreach ( $allowed_prefixes as $prefix ) {
                    if ( 0 === strpos( $name, $prefix ) ) {
                        $ok = true;
                        break;
                    }
                }

                if ( ! $ok ) {
                    $refused[] = $name;
                    continue;
                }

                update_option( $name, $value );
                $written[] = $name;
            }

            return [ 'written' => $written, 'refused' => $refused ];
        },
    ] );

    /** Read an option back, so a spec can assert on what setup actually wrote. */
    register_rest_route( 'erp-pw/v1', '/options', [
        'methods'             => 'GET',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function ( WP_REST_Request $request ) {
            $names = array_filter( array_map( 'trim', explode( ',', (string) $request->get_param( 'names' ) ) ) );
            $out   = [];

            foreach ( $names as $name ) {
                $out[ $name ] = get_option( $name );
            }

            return $out;
        },
    ] );

    /**
     * Flush the erp-pro employee-count cache. `count_users()` caches for an hour
     * under `erp-pro-get-employees-count`, so a seat-limit test that terminates
     * an employee cannot observe the change without this.
     */
    register_rest_route( 'erp-pw/v1', '/flush-cache', [
        'methods'             => 'POST',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function () {
            wp_cache_delete( 'erp-pro-get-employees-count', 'erp' );
            wp_cache_flush();

            return [ 'flushed' => true ];
        },
    ] );

    register_rest_route( 'erp-pw/v1', '/php-errors', [
        'methods'             => 'GET',
        'permission_callback' => 'erp_pw_authorized',
        'callback'            => function () {
            $log = WP_CONTENT_DIR . '/../wp-data/debug.log';

            if ( ! file_exists( $log ) ) {
                return [ 'lines' => [], 'note' => 'no debug.log' ];
            }

            $lines = array_slice( file( $log, FILE_IGNORE_NEW_LINES ), -200 );
            $fatal = array_values( array_filter( $lines, function ( $l ) {
                return false !== stripos( $l, 'Fatal error' ) || false !== stripos( $l, 'Parse error' );
            } ) );

            return [ 'lines' => $lines, 'fatal' => $fatal ];
        },
    ] );
} );

// phpcs:enable
