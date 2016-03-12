<?php
namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class New_Employee_Welcome extends Email {

    use Hooker;

    function __construct() {
        $this->id             = 'employee-welcome';
        $this->title          = __( 'Employee welcome', 'wp-erp' );
        $this->description    = __( 'Welcome email to new employees.', 'wp-erp' );

        $this->subject        = __( 'Welcome {employee_name} to {company_name}', 'wp-erp');
        $this->heading        = __( 'Welcome Onboard!', 'wp-erp');

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
            'login-info'      => '{login_info}'
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

        $employee          = new \WeDevs\ERP\HRM\Employee( $this->employee_id );
        $company           = new \WeDevs\ERP\Company();

        $this->recipient   = $employee->user_email;
        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'       => $employee->get_full_name(),
            'first-name'      => $employee->first_name,
            'last-name'       => $employee->last_name,
            'job-title'       => $employee->get_job_title(),
            'dept-title'      => $employee->get_department_title(),
            'status'          => $employee->get_status(),
            'type'            => $employee->get_type(),
            'joined-date'     => $employee->get_joined_date(),
            'reporting-to'    => $employee->get_reporting_to() ? $employee->get_reporting_to()->get_full_name() : '',
            'compnay-name'    => $company->name,
            'compnay-address' => $company->get_formatted_address(),
            'compnay-phone'   => $company->phone,
            'compnay-website' => $company->website,
            'login-info'      => ''
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
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $employee->user_login ) );

            $password = '<a class="button sm" href="' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($employee->user_login), 'login') . '">' . __( 'Set Your Password', 'wp-erp' ) . '</a>';

            $login_info = '<h3>' . __( 'Login Details:', 'wp-erp' ) . '</h3>';
            $login_info .= sprintf( __( 'Username: <em>%s</em>', 'wp-erp' ), $employee->user_login) . '<br>';
            $login_info .= sprintf( __( 'Password: %s', 'wp-erp' ), $password ) . '<br>';

            $this->replace['login-info'] = $login_info;
        }

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

    /**
     * get_content_html function.
     *
     * @access public
     * @return string
     */
    function get_content_html() {
        $message = $this->get_template_content( WPERP_INCLUDES . '/email/email-body.php', $this->get_args() );

        return $this->format_string( $message );
    }

    /**
     * get_content_plain function.
     *
     * @access public
     * @return string
     */
    function get_content_plain() {
        $message = $this->get_template_content( WPERP_INCLUDES . '/email/email-body.php', $this->get_args() );

        return $message;
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = [
            [
                'title'       => __( 'Subject', 'wp-erp' ),
                'id'          => 'subject',
                'type'        => 'text',
                'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wp-erp' ), $this->subject ),
                'placeholder' => '',
                'default'     => $this->subject,
                'desc_tip'    => true
            ],
            [
                'title'       => __( 'Email Heading', 'wp-erp' ),
                'id'          => 'heading',
                'type'        => 'text',
                'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wp-erp' ), $this->heading ),
                'placeholder' => '',
                'default'     => $this->heading,
                'desc_tip'    => true
            ],
            [
                'title'             => __( 'Email Body', 'wp-erp' ),
                'type'              => 'wysiwyg',
                'id'                => 'body',
                'description'       => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wp-erp' ), $this->heading ),
                'placeholder'       => '',
                'default'           => '',
                'desc_tip'          => true,
                'custom_attributes' => [
                    'rows' => 5,
                    'cols' => 45
                ]
            ],
            [
                'type' => $this->id . '_help_texts'
            ]
        ];
    }

    /**
     * Template tags
     *
     * @return void
     */
    function replace_keys() {
        ?>
        <tr valign="top" class="single_select_page">
            <th scope="row" class="titledesc"><?php _e( 'Template Tags', 'wp-erp' ); ?></th>
            <td class="forminp">
                <em><?php _e( 'You may use these template tags inside subject, heading, body and those will be replaced by original values', 'wp-erp' ); ?></em>:
                <?php echo '<code>' . implode( '</code>, <code>', $this->find ) . '</code>'; ?>
            </td>
        </tr>
        <?php
    }
}
