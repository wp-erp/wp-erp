<?php
namespace WeDevs\ERP\CRM;

/**
 * Loads CRM users admin area
 *
 * @since 1.0
 *
 * @package WP-ERP\CRM
 * @subpackage Administration
 */
class User_Profile {

    /**
     * The CRM users admin loader
     *
     * @package WP-ERP\CRM
     * @subpackage Administration
     */
    public function __construct() {
        $this->setup_actions();
    }

    /**
     * Setup the admin hooks, actions and filters
     *
     * @since 1.0
     *
     * @return void
     */
    function setup_actions() {

        // Bail if in network admin
        if ( is_network_admin() ) {
            return;
        }

        add_action( 'erp_user_profile_role', array( $this, 'role' ) );
        add_action( 'erp_update_user', array( $this, 'update_user' ), 10, 2 );
    }

    /**
     * Update user role from user profile
     *
     * @since 1.0
     *
     * @param  integer $user_id
     * @param  object $post
     *
     * @return void
     */
    function update_user( $user_id, $post ) {

        $new_crm_manager_role = isset( $post['crm_manager'] ) ? sanitize_text_field( $post['crm_manager'] ) : false;
        $new_crm_agent_role   = isset( $post['crm_agent'] ) ? sanitize_text_field( $post['crm_agent'] ) : false;

        if ( ! $new_crm_manager_role && ! $new_crm_agent_role ) {
            return;
        }

        // Bail if current user cannot promote the passing user
        if ( ! current_user_can( 'promote_user', $user_id ) ) {
            return;
        }

        $user = get_user_by( 'id', $user_id );

        if ( $new_crm_manager_role ) {
            $user->add_role( $new_crm_manager_role );
        } else {
            $user->remove_role( erp_crm_get_manager_role() );
        }

        if ( $new_crm_agent_role ) {
            $user->add_role( $new_crm_agent_role );
        } else {
            $user->remove_role( erp_crm_get_agent_role() );
        }
    }

    /**
     * Show roles fields
     *
     * @since 1.0
     *
     * @param  object $profileuser
     *
     * @return html|void
     */
    function role( $profileuser ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $is_manager = in_array( erp_crm_get_manager_role(), $profileuser->roles ) ? 'checked' : '';
        $is_agent   = in_array( erp_crm_get_agent_role(), $profileuser->roles ) ? 'checked' : '';
        ?>
        <label for="erp-crm-manager">
            <input type="checkbox" id="erp-crm-manager" <?php echo esc_attr( $is_manager ); ?> name="crm_manager" value="<?php echo esc_attr( erp_crm_get_manager_role() ); ?>">
            <span class="description"><?php esc_attr_e( 'CRM Manager', 'erp' ); ?></span>
        </label>

        <label for="erp-crm-agent">
            <input type="checkbox" id="erp-crm-agent" <?php echo esc_html( $is_agent ); ?> name="crm_agent" value="<?php echo esc_attr( erp_crm_get_agent_role() ); ?>">
            <span class="description"><?php esc_attr_e( 'CRM Agent', 'erp' ); ?></span>
        </label>
        <?php
    }
}
