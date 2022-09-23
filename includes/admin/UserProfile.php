<?php

namespace WeDevs\ERP\Admin;

/**
 * Loads HR users admin area
 */
class UserProfile {

    /**
     * The HR users admin loader
     */
    public function __construct() {
        $this->setup_actions();
    }

    /**
     * Setup the admin hooks, actions and filters
     *
     * @return void
     */
    public function setup_actions() {

        // Bail if in network admin
        if ( is_network_admin() ) {
            return;
        }

        // User profile edit/display actions
        add_action( 'edit_user_profile', [ $this, 'role_display' ] );
        add_action( 'show_user_profile', [ $this, 'role_display' ] );
        add_action( 'profile_update', [ $this, 'profile_update_role' ] );
    }

    /**
     * Default interface for setting a HR role
     *
     * @param WP_User $profileuser User data
     *
     * @return bool Always false
     */
    public static function role_display( $profileuser ) {
        // Bail if current user cannot edit users
        if ( ! current_user_can( 'edit_user', $profileuser->ID ) || ! current_user_can( 'manage_options' ) ) {
            return;
        } ?>

        <h3><?php esc_html_e( 'WP ERP Role', 'erp' ); ?></h3>
        <?php wp_nonce_field( 'user_profile_update_role', '_erp_nonce' ); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="erp-hr-role"><?php esc_html_e( 'Role', 'erp' ); ?></label></th>
                    <td>
                        <?php do_action( 'erp_user_profile_role', $profileuser ); ?>
                    </td>
                </tr>

            </tbody>
        </table>

        <?php
    }

    public static function profile_update_role( $user_id = 0 ) {
        // verify nonce
        if ( ! isset( $_REQUEST['_erp_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_erp_nonce'] ), 'user_profile_update_role' ) ) {
            return;
        }

        // Bail if no user ID was passed
        if ( empty( $user_id ) ) {
            return;
        }

        // Bail if current user cannot edit users
        if ( ! current_user_can( 'edit_user', $user_id ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        do_action( 'erp_update_user', $user_id, map_deep( wp_unslash( $_POST ), 'sanitize_text_field' ) );
    }
}
