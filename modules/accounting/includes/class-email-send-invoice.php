<?php
namespace WeDevs\ERP\Accounting\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class Accounting_Invoice_Email extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'accouting-send-invoice';
        $this->find = [];
        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function trigger( $receiver_emails = [], $subject = '', $body = '', $attachement = '' ) {

        foreach ( $receiver_emails as $email ) {
            $this->send( $email, $subject, $body, '', $attachement );
        }
    }

    /**
     * Get template args
     *
     * @return array
     */
    function get_args() {
        return [
            'email_heading' => $this->get_heading(),
            'email_body'    => wpautop( $this->get_option( 'body' ) )
        ];
    }

}