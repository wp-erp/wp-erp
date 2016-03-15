<?php
namespace WeDevs\ERP\CRM;

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
        add_action( 'erp_user_profile_role', array( $this, 'role' ) );
        add_action( 'erp_update_user', array( $this, 'update_user' ), 10, 2 );
    }

    function update_user( $user_id, $post ) {

        // HR role we want the user to have
        $new_role = isset( $post['crm_manager'] ) ? sanitize_text_field( $post['crm_manager'] ) : false;

        // Bail if current user cannot promote the passing user
        if ( ! current_user_can( 'promote_user', $user_id ) ) {
            return;
        }

        // Set the new HR role
        $user = get_user_by( 'id', $user_id );

        if ( $new_role ) {
            $user->add_role( $new_role );
        } else {
            $user->remove_role( erp_crm_get_manager_role() );
        }
        
    }

    function role( $profileuser ) { 
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $checked = in_array( erp_crm_get_manager_role(), $profileuser->roles ) ? 'checked' : '';
        ?>
        <label for="erp-crm-manager">
            <input type="checkbox" id="erp-crm-manager" <?php echo $checked; ?> name="crm_manager" value="<?php echo erp_crm_get_manager_role(); ?>">
            <span class="description"><?php _e( 'CRM Manager', 'WP-ERP' ); ?></span>
        </label>
        <?php
    }

}
