<?php

namespace WeDevs\ERP\Accounting\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Approved Leave Request
 */
class TransactionalEmailPurchaseOrder extends Email {
    use Hooker;

    public function __construct() {
        $this->id             = 'transectional-email-purchase-order';
        $this->title          = __( 'New transaction purchase order', 'erp' );
        $this->description    = __( 'New purchase order notification alert', 'erp' );

        $this->subject        = __( 'An purchase order has been created', 'erp' );
        $this->heading        = __( 'New transaction purchase order', 'erp' );

        $this->find = [
            'vendor_name'  => '{vendor_name}',
            'invoice_ID'   => '{invoice_ID}',
            'amount'       => '{amount}',
            'trn_date'     => '{trn_date}',
            'company_name' => '{company_name}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    public function trigger( $receiver_email, $attachment, $voucher_no, $company ) {
        $this->recipient   = $receiver_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $voucher_details = erp_acct_get_purchase( $voucher_no );

        $this->replace = [
            'vendor_name'  => $voucher_details['vendor_name'],
            'invoice_ID'   => $voucher_no,
            'amount'       => $voucher_details['amount'],
            'trn_date'     => $voucher_details['trn_date'],
            'company_name' => $company->name,
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $attachment );
    }
}
