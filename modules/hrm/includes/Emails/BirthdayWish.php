<?php

namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Birthday wish email
 */
class BirthdayWish extends Email {
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
        $this->id             = 'birthday-wish';
        $this->title          = __( 'Birthday Wish', 'erp' );
        $this->description    = __( 'Birthday wish email to employees.', 'erp' );

        $this->subject        = __( 'Birthday Wish to {full_name}', 'erp' );
        $this->heading        = __( 'Happy Birthday :)', 'erp' );

        $this->find = [
            'full-name'       => '{full_name}',
            'first-name'      => '{first_name}',
            'last-name'       => '{last_name}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function trigger( $employee_user_id = null ) {
        if ( ! $employee_user_id ) {
            return;
        }

        $employee          = new \WeDevs\ERP\HRM\Employee( $employee_user_id );

        $this->recipient   = $employee->user_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'       => $employee->get_full_name(),
            'first-name'      => $employee->first_name,
            'last-name'       => $employee->last_name,
        ];

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
