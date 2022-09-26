<?php

namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * HR Email handler class
 */
class Emailer {
    use Hooker;

    public function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    public function register_emails( $emails ) {
        $emails['NewTaskAssigned']        = new Emails\NewTaskAssigned();
        $emails['NewContactAssigned']     = new Emails\NewContactAssigned();
        $emails['BirthdayGreetings']       = new Emails\BirthdayGreetings();

        return apply_filters( 'erp_crm_email_classes', $emails );
    }
}
