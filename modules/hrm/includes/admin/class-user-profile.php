<?php
namespace WeDevs\ERP\HRM\Admin;

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
        add_action( 'edit_user_profile', array( $this, 'role_display' ) );
        add_action( 'profile_update', array( $this, 'profile_update_role' ) );
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
        if ( ! current_user_can( 'edit_user', $profileuser->ID ) ) {
            return;
        }

        $hr_roles = erp_hr_get_roles();

        ?>

        <h3><?php esc_html_e( 'WP-ERP HR Role', 'wp-erp' ); ?></h3>

        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="erp-hr-role"><?php esc_html_e( 'HR Role', 'wp-erp' ); ?></label></th>
                    <td>

                        <?php $user_role = erp_hr_get_user_role( $profileuser->ID ); ?>

                        <select name="erp-hr-role" id="erp-hr-role">

                            <?php if ( ! empty( $user_role ) ) : ?>

                                <option value=""><?php esc_html_e( '&mdash; No role for HR &mdash;', 'wp-erp' ); ?></option>

                            <?php else : ?>

                                <option value="" selected="selected"><?php esc_html_e( '&mdash; No role for HR &mdash;', 'wp-erp' ); ?></option>

                            <?php endif; ?>

                            <?php foreach ( $hr_roles as $role => $details ) : ?>
                                <?php if ( $details['public'] == true ) continue; ?>

                                <option <?php selected( $user_role, $role ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo translate_user_role( $details['name'] ); ?></option>

                            <?php endforeach; ?>

                        </select>
                    </td>
                </tr>

            </tbody>
        </table>

        <?php
    }

    public static function profile_update_role( $user_id = 0 ) {

        // Bail if no user ID was passed
        if ( empty( $user_id ) )
            return;

        // Bail if no role
        if ( ! isset( $_POST['erp-hr-role'] ) )
            return;

        // HR role we want the user to have
        $new_role = sanitize_text_field( $_POST['erp-hr-role'] );
        $hr_role  = erp_hr_get_user_role( $user_id );

        // Bail if no role change
        if ( $new_role === $hr_role ) {
            return;
        }

        // Bail if current user cannot promote the passing user
        if ( ! current_user_can( 'promote_user', $user_id ) ) {
            return;
        }

        // Set the new HR role
        $user = get_user_by( 'id', $user_id );

        // Remove the old role
        if ( ! empty( $role ) ) {
            $user->remove_role( $hr_role );
        }

        // Add the new role
        if ( !empty( $new_role ) ) {
            $user->add_role( $new_role );
        }
    }
}
