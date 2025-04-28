<?php

namespace WeDevs\ERP\HRM\Emails;

use DateTime;
use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Work anniversary wish email
 */
class HiringAnniversaryWish extends Email {
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
        $this->id             = 'hiring-anniversary-wish';
        $this->title          = __( 'Work Anniversary Wish', 'erp' );
        $this->description    = __( 'Work anniversary wish email to employees.', 'erp' );

        $this->subject        = __( 'Congratulation for Your Work Anniversary', 'erp' );
        $this->heading        = __( 'Congratulation for Passing One More Year With Us :)', 'erp' );

        $this->find = [
            'full-name'       => '{full_name}',
            'first-name'      => '{first_name}',
            'last-name'       => '{last_name}',
            'total-year'      => '{total_year}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function get_year_diff( $hiring_date ) {
        //return $hiring_date;
        $d1   = new DateTime( current_time( 'Y-m-d' ) );
        $d2   = new DateTime( $hiring_date );
        $diff = $d2->diff( $d1 );

        return $diff->y;
    }

    /**
     * Trigger sending email
     *
     * @param int    $employee_user_id
     * @param string $hiring_date
     *
     * @return void
     */
    public function trigger( $employee_user_id = null, $hiring_date = null ) {
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
            'total-year'      => $this->get_year_diff( $hiring_date ),
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
