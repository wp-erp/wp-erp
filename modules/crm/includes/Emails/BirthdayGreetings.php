<?php

namespace WeDevs\ERP\CRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Birthday greetings email
 */
class BirthdayGreetings extends Email {
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
        $this->id             = 'birthday-greetings';
        $this->title          = __( 'Birthday Greetings To Contacts', 'erp' );
        $this->description    = __( 'Birthday greetings email to contacts.', 'erp' );

        $this->subject        = __( 'Birthday Greetings to {first_name} {last_name}', 'erp' );
        $this->heading        = __( 'Happy Birthday :)', 'erp' );

        $this->find = [
            'first-name'      => '{first_name}',
            'last-name'       => '{last_name}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function trigger() {
        $contacts =  erp_get_peoples( [
            'type'  => 'contact',
        ] );

        if ( ! $contacts ) {
            return;
        }

        foreach ( $contacts as $contact ) {
            $birthday     = erp_people_get_meta( $contact->id, 'date_of_birth', true );
            $current_date = gmdate( 'Y-m-d' );

            if ( $birthday != $current_date ) {
                continue;
            }

            $this->recipient   = $contact->email;
            $this->heading     = $this->get_option( 'heading', $this->heading );
            $this->subject     = $this->get_option( 'subject', $this->subject );

            $first_name        = isset( $contact->first_name ) ? $contact->first_name : '';
            $last_name         = isset( $contact->last_name ) ? $contact->last_name : '';
            $this->replace     = [
                'first-name'      => $first_name,
                'last-name'       => $last_name,
            ];

            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }

    /**
     * Get template args
     *
     * @return array
     */
    public function get_args() {
        return [
            'email_heading' => $this->get_heading(),
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }
}
