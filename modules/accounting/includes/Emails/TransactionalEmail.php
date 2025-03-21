<?php

namespace WeDevs\ERP\Accounting\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * New transaction invoice email
 */
class TransactionalEmail extends Email {
    use Hooker;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $heading;

    /**
     * @var array
     */
    public $find;

    /**
     * @var array
     */
    public $replace;

    public function __construct() {
        $this->id             = 'transectional-email';
        $this->title          = __( 'New transaction invoice', 'erp' );
        $this->description    = __( 'New invoice notification alert', 'erp' );

        $this->subject        = __( 'New invoice has been created', 'erp' );
        $this->heading        = __( 'New transaction invoice', 'erp' );

        $this->find = [
            'customer_name' => '{customer_name}',
            'invoice_ID'    => '{invoice_ID}',
            'amount'        => '{amount}',
            'due_date'      => '{due_date}',
            'company_name'  => '{company_name}',
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

        $voucher_details = erp_acct_get_invoice( $voucher_no );

        $this->replace = [
            'customer_name' => $voucher_details['customer_name'],
            'invoice_ID'    => $voucher_no,
            'amount'        => $voucher_details['amount'],
            'due_date'      => $voucher_details['due_date'],
            'company_name'  => $company->name,
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $attachment );
    }
}
