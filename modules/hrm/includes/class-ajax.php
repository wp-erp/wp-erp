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
        add_action( 'wp_ajax_erp-hr-new-dept', array($this, 'department_create') );
        add_action( 'wp_ajax_erp-hr-del-dept', array($this, 'department_delete') );
        add_action( 'wp_ajax_erp-hr-get-dept', array($this, 'department_get') );
        add_action( 'wp_ajax_erp-hr-update-dept', array($this, 'department_create') );

        add_action( 'wp_ajax_erp-hr-new-desig', array($this, 'designation_create') );
        add_action( 'wp_ajax_erp-hr-get-desig', array($this, 'designation_get') );
        add_action( 'wp_ajax_erp-hr-update-desig', array($this, 'designation_create') );
        add_action( 'wp_ajax_erp-hr-del-desig', array($this, 'designation_delete') );
    }

    /**
     * Verify request nonce
     *
     * @param  string  the nonce action name
     *
     * @return void
     */
    public function verify_nonce( $action ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], $action ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'wp-erp' ) );
        }
    }

    /**
     * Get a department
     *
     * @return void
     */
    public function department_get() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            $department = new \WeDevs\ERP\HRM\Department( $id );
            wp_send_json_success( $department );
        }

        wp_send_json_success( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Create a new department
     *
     * @return void
     */
    public function department_create() {
        $this->verify_nonce( 'erp-new-dept' );

        // @TODO: check permission

        $title   = isset( $_POST['title'] ) ? trim( strip_tags( $_POST['title'] ) ) : '';
        $desc    = isset( $_POST['dept-desc'] ) ? trim( strip_tags( $_POST['dept-desc'] ) ) : '';
        $dept_id = isset( $_POST['dept_id'] ) ? intval( $_POST['dept_id'] ) : 0;
        $lead    = isset( $_POST['lead'] ) ? intval( $_POST['lead'] ) : 0;
        $parent  = isset( $_POST['parent'] ) ? intval( $_POST['parent'] ) : 0;

        // on update, ensure $parent != $dept_id
        if ( $dept_id == $parent ) {
            $parent = 0;
        }

        $dept_id = erp_hr_create_department( array(
            'id'          => $dept_id,
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

    /**
     * Delete a department
     *
     * @return void
     */
    public function department_delete() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $id ) {
            // @TODO: check permission
            erp_hr_delete_department( $id );
        }

        wp_send_json_success( __( 'Department has been deleted', 'wp-erp' ) );
    }

    /**
     * Create a new designnation
     *
     * @return void
     */
    function designation_create() {
        $this->verify_nonce( 'erp-new-desig' );

        $title    = isset( $_POST['title'] ) ? trim( strip_tags( $_POST['title'] ) ) : '';
        $desc     = isset( $_POST['desig-desc'] ) ? trim( strip_tags( $_POST['desig-desc'] ) ) : '';
        $desig_id = isset( $_POST['desig_id'] ) ? intval( $_POST['desig_id'] ) : 0;

        $desig_id = erp_hr_create_designation( array(
            'id'          => $desig_id,
            'company_id'  => erp_get_current_company_id(),
            'title'       => $title,
            'description' => $desc
        ) );

        if ( is_wp_error( $desig_id ) ) {
            wp_send_json_error( $desig_id->get_error_message() );
        }

        wp_send_json_success( array(
            'id'       => $desig_id,
            'title'    => $title,
            'employee' => 0
        ) );
    }

    /**
     * Get a department
     *
     * @return void
     */
    public function designation_get() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            $designation = new \WeDevs\ERP\HRM\Designation( $id );
            wp_send_json_success( $designation );
        }

        wp_send_json_success( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Delete a department
     *
     * @return void
     */
    public function designation_delete() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $id ) {
            // @TODO: check permission
            erp_hr_delete_designation( $id );
        }

        wp_send_json_success( __( 'Designation has been deleted', 'wp-erp' ) );
    }
}

new Ajax_Handler();