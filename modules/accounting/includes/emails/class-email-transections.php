<?php
namespace WeDevs\ERP\Accounting\Includes\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Approved Leave Request
 */
class Transectional_Email extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'transectional-email';
        $this->title          = __( 'New transection', 'erp' );
        $this->description    = __( 'A new transection has been made just now', 'erp' );

        $this->subject        = __( 'A new transection has been made', 'erp');
        $this->heading        = __( 'New transection', 'erp');

        $this->find = [
            'full-name'    => '{employee_name}',
            'leave_type'   => '{leave_type}',
            'date_from'    => '{date_from}',
            'date_to'      => '{date_to}',
            'no_days'      => '{no_days}',
            'reason'       => '{reason}',
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

    public function trigger( $request_id = null ) {
        $request = erp_hr_get_leave_request( $request_id );

        if ( ! $request ) {
            return;
        }

        $employee          = new \WeDevs\ERP\HRM\Employee( intval( $request->user_id ) );

        $this->recipient   = $employee->user_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'    => $request->display_name,
            'leave_type'   => $request->policy_name,
            'date_from'    => erp_format_date( $request->start_date ),
            'date_to'      => erp_format_date( $request->end_date ),
            'no_days'      => $request->days,
            'reason'       => $request->reason,
        ];

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

}
