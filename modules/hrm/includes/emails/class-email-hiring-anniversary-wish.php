<?php
namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Birthday wish
 */
class Hiring_Anniversary_Wish extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'hiring-anniversary-wish';
        $this->title          = __( 'Work Anniversary Wish', 'erp' );
        $this->description    = __( 'Work anniversary wish email to employees.', 'erp' );

        $this->subject        = __( 'Congratulation for Your Work Anniversary', 'erp');
        $this->heading        = __( 'Congratulation for Passing One More Year With Us :)', 'erp');

        $this->find = [
            'full-name'       => '{full_name}',
            'first-name'      => '{first_name}',
            'last-name'       => '{last_name}'
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
            'last-name'       => $employee->last_name
        ];

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
