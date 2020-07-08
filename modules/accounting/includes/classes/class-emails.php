<?php
namespace WeDevs\ERP\Accounting;

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

        $emails['Transactional_Email']          = new Includes\Emails\Transactional_Email();
        $emails['Transactional_Email_Payments'] = new Includes\Emails\Transactional_Email_Payments();

        return $emails;
    }
}
