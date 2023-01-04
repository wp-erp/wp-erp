<?php

namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Goverment holiday reminder email
 */
class GovtHolidayReminder extends Email {
    use Hooker;

    public function __construct() {
        $this->id             = 'govt-holiday-reminder';
        $this->title          = __( 'Upcoming government holiday reminder', 'erp' );
        $this->description    = __( 'Government holiday reminder email to employees.', 'erp' );

        $this->subject        = __( 'Upcoming government holiday reminder', 'erp' );
        $this->heading        = __( 'Reminder', 'erp' );

        $this->find = [
            'full-name'        => '{full_name}',
            'first-name'       => '{first_name}',
            'last-name'        => '{last_name}',
            'holiday-name'     => '{holiday_name}',
            'holiday-duration' => '{holiday_duration}',
            'reopen-day'       => '{reopen_day}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function trigger( $employee_user_id = null, $holiday_name = '', $holiday_duration = '', $reopen_day = '' ) {
        if ( ! $employee_user_id ) {
            return;
        }

        $employee          = new \WeDevs\ERP\HRM\Employee( $employee_user_id );

        $this->recipient   = $employee->user_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'        => $employee->get_full_name(),
            'first-name'       => $employee->first_name,
            'last-name'        => $employee->last_name,
            'holiday-name'     => $holiday_name,
            'holiday-duration' => $holiday_duration,
            'reopen-day'       => $reopen_day,
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
