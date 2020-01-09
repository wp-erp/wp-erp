<?php
namespace WeDevs\ERP\Admin;

/**
 * Loads HR users admin area
 *
 * @package WP-ERP\HR
 * @subpackage Administration
 */
class User_Profile {

    /**
     * The HR users admin loader
     *
     * @package WP-ERP\HR
     * @subpackage Administration
     */
    public function __construct() {
        $this->setup_actions();
    }

    /**
     * Setup the admin hooks, actions and filters
     *
     * @return void
     */
    function setup_actions() {

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
        if ( ! current_user_can( 'edit_user', $profileuser->ID ) || !current_user_can( 'manage_options') ) {
            return;
        }

        ?>

        <h3><?php esc_html_e( 'WP ERP Role', 'erp' ); ?></h3>

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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-nonce' ) ) {
            //
        }

        // Bail if no user ID was passed
        if ( empty( $user_id ) ) {
            return;
        }

        do_action( 'erp_update_user', $user_id, $_POST );
    }
}
