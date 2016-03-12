<?php
namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;

/**
 * Employee welcome
 */
class Rejected_Leave_Request extends Email {

    function __construct() {
        $this->email_type     = 'html';
        $this->id             = 'approved-leave-request';
        $this->title          = __( 'Rejected Leave Request', 'wp-erp' );
        $this->description    = __( 'Rejected leave request notification to employee.', 'wp-erp' );

        $this->template_html  = WPERP_HRM_VIEWS . '/emails/employee-welcome.php';
        $this->template_plain = WPERP_HRM_VIEWS . '/emails/plain/employee-welcome.php';

        $this->subject        = __( 'Welcome {employee_name} to {company_name}', 'wp-erp');
        $this->heading        = __( 'Welcome Onboard!', 'wp-erp');
    }

    function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_subject' => $this->subject,
            'employee'      => new \WeDevs\ERP\HRM\Employee( $this->employee_id )
        ];
    }

    public function trigger( $employee_id = null ) {
        $this->employee_id = $employee_id;

        // echo $this->get_content();
        echo $this->style_inline( $this->get_content() );
    }

    /**
     * get_content_html function.
     *
     * @access public
     * @return string
     */
    function get_content_html() {
        extract( $this->get_args() );

        ob_start();
        include $this->template_html;
        return ob_get_clean();
    }

    /**
     * get_content_plain function.
     *
     * @access public
     * @return string
     */
    function get_content_plain() {
        extract( $this->get_args() );

        ob_start();
        include $this->template_plain;
        return ob_get_clean();
    }
}