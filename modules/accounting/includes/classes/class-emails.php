<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\Traits\Hooker;
//use WeDevs\ERP\Accounting\Includes\Emails\Transectional_Email as Transectional_Email;
/**
 * HR Email handler class
 */
class Emailer {

    use Hooker;

    function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    function register_emails( $emails ) {

        $emails['Transectional_Email'] = new Includes\Emails\Transectional_Email();

        return $emails;
    }
}
