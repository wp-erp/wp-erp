<?php

namespace WeDevs\ERP\CRM\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * New Task Assigned
 */
class NewTaskAssigned extends Email {
    use Hooker;

    public function __construct() {
        $this->id          = 'new-task-assigned';
        $this->title       = __( 'New Task Assigned', 'erp' );
        $this->description = __( 'New task assigned notification to employee.', 'erp' );

        $this->subject     = __( 'New task has been assigned to you', 'erp' );
        $this->heading     = __( 'New Task Assigned', 'erp' );

        $this->find = [
            'employee_name' => '{employee_name}',
            'task_title'    => '{task_title}',
            'due_date'      => '{due_date}',
            'created_by'    => '{created_by}',
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

    public function trigger( $data ) {
        global $current_user;

        $activity = \WeDevs\ERP\CRM\Models\Activity::where( [
            'id'   => intval( $data['activity_id'] ),
            'type' => 'tasks',
        ] )->first();

        if ( ! $activity ) {
            return;
        }

        $extra = json_decode( base64_decode( $activity->extra ) );

        $this->heading = $this->get_option( 'heading', $this->heading );
        $this->subject = $this->get_option( 'subject', $this->subject );

        $task_view_url      = admin_url( 'admin.php?page=erp-crm&section=task&sub-section=tasks' );
        $task_description   = $activity->message . "<br><a style='background: #1A9ED4;color: #fff;padding: 6px 10px;margin: 0;border-radius: 3px;display: inline-block;text-decoration: none;' href='$task_view_url' target='_blank'> ". __( 'View Tasks', 'erp' ) ." </a><br><br>";

        foreach ( $data['user_ids'] as $id ) {
            $employee = new \WeDevs\ERP\HRM\Employee( intval( $id ) );

            if ( $employee ) {
                $this->recipient    = $employee->user_email;

                $this->replace      = [
                    'employee_name'    => $employee->get_full_name(),
                    'task_title'       => $extra->task_title,
                    'task_description' => $task_description,
                    'due_date'         => erp_format_date( $activity->start_date ),
                    'created_by'       => $current_user->display_name,
                ];

                $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            }
        }
    }
}
