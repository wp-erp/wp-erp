<?php
namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * HR Email handler class
 */
class Emailer {

    use Hooker;

    function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    function register_emails( $emails ) {

        $emails['New_Employee_Welcome']     = new Emails\New_Employee_Welcome();
        $emails['New_Leave_Request']        = new Emails\New_Leave_Request();
        $emails['Approved_Leave_Request']   = new Emails\Approved_Leave_Request();
        $emails['Rejected_Leave_Request']   = new Emails\Rejected_Leave_Request();
        $emails['Birthday_Wish']            = new Emails\Birthday_Wish();
        $emails['Hiring_Anniversary_Wish']  = new Emails\Hiring_Anniversary_Wish();

        return apply_filters( 'erp_hr_email_classes', $emails );
    }
}
