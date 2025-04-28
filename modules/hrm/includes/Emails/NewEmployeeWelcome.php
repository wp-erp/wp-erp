<?php

namespace WeDevs\ERP\HRM\Emails;

use PasswordHash;
use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class NewEmployeeWelcome extends Email {
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

    /**
     * @var int
     */
    public $employee_id;

    public function __construct() {
        $this->id             = 'employee-welcome';
        $this->title          = __( 'Employee welcome', 'erp' );
        $this->description    = __( 'Welcome email to new employees.', 'erp' );

        $this->subject        = __( 'Welcome {employee_name} to {company_name}', 'erp' );
        $this->heading        = __( 'Welcome Onboard!', 'erp' );

        $this->find = [
            'full-name'       => '{full_name}',
            'first-name'      => '{first_name}',
            'last-name'       => '{last_name}',
            'job-title'       => '{job_title}',
            'dept-title'      => '{dept_title}',
            'status'          => '{status}',
            'type'            => '{type}',
            'joined-date'     => '{joined_date}',
            'reporting-to'    => '{reporting_to}',
            'compnay-name'    => '{company_name}',
            'compnay-address' => '{company_address}',
            'compnay-phone'   => '{company_phone}',
            'compnay-website' => '{company_website}',
            'login-info'      => '{login_info}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    public function trigger( $employee_id = null, $send_login = true ) {
        if ( ! $employee_id ) {
            return;
        }

        // setup variables
        $this->employee_id = $employee_id;

        $employee          = new \WeDevs\ERP\HRM\Employee( $employee_id );
        $company           = new \WeDevs\ERP\Company();

        $this->recipient   = $employee->user_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'       => $employee->get_full_name(),
            'first-name'      => $employee->first_name,
            'last-name'       => $employee->last_name,
            'job-title'       => $employee->get_job_title(),
            'dept-title'      => $employee->get_department( 'view' ),
            'status'          => $employee->get_status(),
            'type'            => $employee->get_type(),
            'joined-date'     => $employee->get_hiring_date(),
            'reporting-to'    => $employee->get_reporting_to() ? erp_hr_get_employee_name( $employee->get_reporting_to() ) : '',
            'compnay-name'    => $company->name,
            'compnay-address' => $company->get_formatted_address(),
            'compnay-phone'   => $company->phone,
            'compnay-website' => $company->website,
            'login-info'      => '',
        ];

        if ( $send_login ) {
            global $wpdb, $wp_hasher;

            // Generate something random for a password reset key.
            $key = wp_generate_password( 20, false );

            // Now insert the key, hashed, into the DB.
            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }

            $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
            $wpdb->update( $wpdb->users, [ 'user_activation_key' => $hashed ], [ 'user_login' => $employee->user_login ] );

            $password = '<a class="button sm" href="' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $employee->user_login ), 'login' ) . '">' . __( 'Set Your Password', 'erp' ) . '</a>';

            $login_info = '<h3>' . __( 'Login Details:', 'erp' ) . '</h3>';
            $login_info .= sprintf( __( 'Username: <em>%s</em>', 'erp' ), $employee->user_login ) . '<br>';
            $login_info .= sprintf( __( 'Password: %s', 'erp' ), $password ) . '<br>';

            $this->replace['login-info'] = $login_info;
        }

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
