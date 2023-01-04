<?php

namespace WeDevs\ERP\CRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * New Contact Assigned
 */
class NewContactAssigned extends Email {
    use Hooker;

    public function __construct() {
        $this->id          = 'new-contact-assigned';
        $this->title       = __( 'New Contact Assigned', 'erp' );
        $this->description = __( 'New contact assigned notification to employee.', 'erp' );

        $this->subject     = __( 'New contact has been assigned to you', 'erp' );
        $this->heading     = __( 'New Contact Assigned', 'erp' );

        $this->find = [
            'employee_name'   => '{employee_name}',
            'contact_name'    => '{contact_name}',
            'created_by'      => '{created_by}',
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

    public function trigger( $contact_id ) {
        global $current_user;

        $contact = erp_get_people( $contact_id );

        if ( ! $contact ) {
            return;
        }
        $last_name         = isset( $contact->last_name ) ? $contact->last_name : '';
        $contact_full_name = $contact->first_name . ' ' . $last_name;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $contact->contact_owner ) );

        $this->recipient    = $employee->user_email;
        $this->replace      = [
            'employee_name' => $employee->get_full_name(),
            'contact_name'  => $contact_full_name,
            'created_by'    => $current_user->display_name,
        ];

        if ( $employee ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }
}
