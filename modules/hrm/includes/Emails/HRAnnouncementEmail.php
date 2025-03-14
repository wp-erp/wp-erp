<?php

namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class HRAnnouncementEmail extends Email {
    use Hooker;

    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    public $find;

    public function __construct() {
        $this->id             = 'hr-announcement-email';
        $this->find           = [];
        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function trigger( $employee_ids = [], $post_id = null ) {
        $post_data         = get_post( $post_id );

        foreach ( $employee_ids as $employee_id ) {
            $employee = new \WeDevs\ERP\HRM\Employee( intval( $employee_id ) );

            if ( $employee ) {
                $this->send( $employee->user_email, $post_data->post_title, wpautop( $post_data->post_content, true ), $this->get_headers(), $this->get_attachments() );
            }
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
