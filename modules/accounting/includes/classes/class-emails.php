<?php

namespace WeDevs\ERP\Accounting;

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
        $emails['Transactional_Email']                  = new Includes\Emails\Transactional_Email();
        $emails['Transactional_Email_Payments']         = new Includes\Emails\Transactional_Email_Payments();
        $emails['Transactional_Email_Purchase']         = new Includes\Emails\Transactional_Email_Purchase();
        $emails['Transactional_Email_Estimate']         = new Includes\Emails\Transactional_Email_Estimate();
        $emails['Transactional_Email_Purchase_Order']   = new Includes\Emails\Transactional_Email_Purchase_Order();
        $emails['Transactional_Email_Pay_Purchase']     = new Includes\Emails\Transactional_Email_Pay_Purchase();

        return $emails;
    }
}
