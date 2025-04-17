<?php

namespace WeDevs\ERP\HRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * New Leave Request
 */
class NewLeaveRequest extends Email {
    use Hooker;

    public function __construct() {
        $this->id             = 'new-leave-request';
        $this->title          =  'New Leave Request';
        $this->description    =  'New leave request notification to HR Manager.';

        $this->subject        =  'New leave request received';
        $this->heading        =  'New Leave Request';

        $this->find = [
            'full-name'    => '{employee_name}',
            'employee-url' => '{employee_url}',
            'leave_type'   => '{leave_type}',
            'date_from'    => '{date_from}',
            'date_to'      => '{date_to}',
            'no_days'      => '{no_days}',
            'reason'       => '{reason}',
            'requests_url' => '{requests_url}',
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

    /**
     * Trigger sending email
     *
     * @since 1.0.0
     * @since 1.2.0 Send single email to multiple recipients.
     *              Add `erp_new_leave_request_notification_recipients` filter
     *
     * @param int $request_id
     *
     * @return bool
     */
    public function trigger( $request_id = null ) {
        $request = erp_hr_get_leave_request( $request_id );

        if ( ! $request ) {
            return;
        }

        $this->heading     = $this->get_option( 'heading', $this->heading );
        $this->subject     = $this->get_option( 'subject', $this->subject );

        $this->replace = [
            'full-name'    => $request->display_name,
            'employee-url' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $request->user_id ), $request->display_name ),
            'leave_type'   => $request->policy_name,
            'date_from'    => erp_format_date( $request->start_date ),
            'date_to'      => erp_format_date( $request->end_date ),
            'no_days'      => $request->days,
            'reason'       => stripslashes( $request->reason ),
            'requests_url' => sprintf( '<a class="button green" href="%s">%s</a>', admin_url( 'admin.php?page=erp-hr&section=leave' ),  'View Request' ),
        ];

        $subject     = $this->get_subject();
        $content     = $this->get_content();
        $headers     = $this->get_headers();
        $attachments = $this->get_attachments();
        $recipients  = [];

        $managers = get_users( [ 'role' => erp_hr_get_manager_role() ] );

        if ( ! $managers ) {
            return;
        }

        foreach ( $managers as $hr ) {
            $recipients[] = $hr->user_email;
        }

        $recipients = apply_filters( 'erp_new_leave_request_notification_recipients', $recipients, $request );

        return $this->send( $recipients, $subject, $content, $headers, $attachments );
    }

    /**
     * get_content_html function.
     *
     * @return string
     */
    public function get_content_html() {
        $message = $this->get_template_content( WPERP_INCLUDES . '/email/email-body.php', $this->get_args() );

        return $this->format_string( $message );
    }

    /**
     * get_content_plain function.
     *
     * @return string
     */
    public function get_content_plain() {
        $message = $this->get_template_content( WPERP_INCLUDES . '/email/email-body.php', $this->get_args() );

        return $message;
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = [
            [
                'title'       =>  'Subject',
                'id'          => 'subject',
                'type'        => 'text',
                'description' => sprintf(  'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.',  $this->subject ),
                'placeholder' => '',
                'default'     => $this->subject,
                'desc_tip'    => true,
            ],
            [
                'title'       =>  'Email Heading',
                'id'          => 'heading',
                'type'        => 'text',
                'description' => sprintf(  'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.',  $this->heading ),
                'placeholder' => '',
                'default'     => $this->heading,
                'desc_tip'    => true,
            ],
            [
                'title'             =>  'Email Body',
                'type'              => 'wysiwyg',
                'id'                => 'body',
                'description'       => sprintf(  'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.',  $this->heading ),
                'placeholder'       => '',
                'default'           => '',
                'desc_tip'          => true,
                'custom_attributes' => [
                    'rows' => 5,
                    'cols' => 45,
                ],
            ],
            [
                'type' => $this->id . '_help_texts',
            ],
        ];
    }

    /**
     * Template tags
     *
     * @return void
     */
    public function replace_keys() {
        ?>
        <tr valign="top" class="single_select_page">
            <th scope="row" class="titledesc"><?php esc_html_e( 'Template Tags', 'erp' ); ?></th>
            <td class="forminp">
                <em><?php esc_html_e( 'You may use these template tags inside subject, heading, body and those will be replaced by original values', 'erp' ); ?></em>:
                <?php echo '<code>' . wp_kses_post( implode( '</code>, <code>', $this->find ) ) . '</code>'; ?>
            </td>
        </tr>
        <?php
    }
}
