<?php
namespace WeDevs\ERP\CRM;

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

        $emails['New_Task_Assigned']        = new Emails\New_Task_Assigned();
        $emails['New_Contact_Assigned']     = new Emails\New_Contact_Assigned();
        $emails['Birthday_Greetings']       = new Emails\Birthday_Greetings();

        return apply_filters( 'erp_crm_email_classes', $emails );
    }
}
