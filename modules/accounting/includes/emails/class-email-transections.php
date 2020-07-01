<?php
namespace WeDevs\ERP\Accounting\Includes\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Approved Leave Request
 */
class Transactional_Email extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'transectional-email';
        $this->title          = __( 'Accounting transaction', 'erp' );
        $this->description    = __( 'Accounting transactional notification alert', 'erp' );

        $this->subject        = __( 'Transaction alert for ', 'erp');
        $this->heading        = __( 'New transaction', 'erp');

        $this->find = [
            'email'    => '{email}'
        ];


        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    public function trigger( $receiver_email, $attachment = '', $type = "invoice" ) {


        $this->recipient   = $receiver_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject ) . " " . $type;

        $this->replace = [
            'email'    => $receiver_email
        ];


        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $attachment );
    }

}
