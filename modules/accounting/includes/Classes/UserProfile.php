<?php

namespace WeDevs\ERP\Accounting\Classes;

/**
 * Loads Accounting users in admin area
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
        add_action( 'erp_user_profile_role', [ $this, 'role' ] );
        add_action( 'erp_update_user', [ $this, 'update_user' ], 10, 1 );
    }

    public function update_user( $user_id ) {
        // verify nonce
        if ( ! isset( $_REQUEST['_erp_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_erp_nonce'] ), 'user_profile_update_role' ) ) {
            return;
        }

        // AC role we want the user to have
        $new_role = isset( $_POST['ac_manager'] ) ? sanitize_text_field( wp_unslash( $_POST['ac_manager'] ) ) : false;

        if ( ! $new_role ) {
            return;
        }

        // Bail if current user cannot promote the passing user
        if ( ! current_user_can( 'promote_user', $user_id ) ) {
            return;
        }

        // Set the new HR role
        $user = get_user_by( 'id', $user_id );

        if ( $new_role ) {
            $user->add_role( $new_role );
        } else {
            $user->remove_role( erp_ac_get_manager_role() );
        }
    }

    public function role( $profileuser ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $checked = in_array( erp_ac_get_manager_role(), $profileuser->roles, true ) ? 'checked' : ''; ?>
        <label for="erp-ac-manager">
            <input type="checkbox" id="erp-ac-manager" <?php echo esc_attr( $checked ); ?> name="ac_manager" value="<?php echo esc_attr( erp_ac_get_manager_role() ); ?>">
            <span class="description"><?php esc_html_e( 'Accounting Manager', 'erp' ); ?></span>
        </label>

        <?php
    }
}
