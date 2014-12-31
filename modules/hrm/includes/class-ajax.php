<?php
namespace WeDevs\ERP\HRM;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    /**
     * Bind all the ajax event for HRM
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_erp-new-dept', array($this, 'department_create') );
    }

    /**
     * Create a new department
     *
     * @return void
     */
    public function department_create() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-new-dept' ) ) {
            wp_send_json_error();
        }

        $title  = isset( $_POST['title'] ) ? strip_tags( $_POST['title'] ) : '';
        $desc   = isset( $_POST['dept-desc'] ) ? strip_tags( $_POST['dept-desc'] ) : '';
        $lead   = isset( $_POST['lead'] ) ? intval( $_POST['lead'] ) : 0;
        $parent = isset( $_POST['parent'] ) ? intval( $_POST['parent'] ) : 0;

        $dept_id = erp_hr_create_department( array(
            'company_id'  => erp_get_current_company_id(),
            'title'       => $title,
            'description' => $desc,
            'lead'        => $lead,
            'parent'      => $parent
        ) );

        if ( is_wp_error( $dept_id ) ) {
            wp_send_json_error( $dept_id->get_error_message() );
        }

        wp_send_json_success( array(
            'id'       => $dept_id,
            'title'    => $title,
            'lead'     => $lead,
            'parent'   => $parent,
            'employee' => 0
        ) );
    }
}

new Ajax_Handler();