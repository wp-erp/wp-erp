<?php

namespace WeDevs\ERP\Accounting\Classes;

use WeDevs\ERP\Accounting\Emails\TransactionalEmail;
use WeDevs\ERP\Accounting\Emails\TransactionalEmailEstimate;
use WeDevs\ERP\Accounting\Emails\TransactionalEmailPayments;
use WeDevs\ERP\Accounting\Emails\TransactionalEmailPayPurchase;
use WeDevs\ERP\Accounting\Emails\TransactionalEmailPurchase;
use WeDevs\ERP\Accounting\Emails\TransactionalEmailPurchaseOrder;
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
        $emails['Transactional_Email']                  = new TransactionalEmail();
        $emails['Transactional_Email_Payments']         = new TransactionalEmailPayments();
        $emails['Transactional_Email_Purchase']         = new TransactionalEmailPayPurchase();
        $emails['Transactional_Email_Estimate']         = new TransactionalEmailEstimate();
        $emails['Transactional_Email_Purchase_Order']   = new TransactionalEmailPurchaseOrder();
        $emails['Transactional_Email_Pay_Purchase']     = new TransactionalEmailPurchase();

        return $emails;
    }
}
