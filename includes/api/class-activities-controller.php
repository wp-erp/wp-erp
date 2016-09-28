<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Activities_Controller extends REST_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp';

    /**
     * Route base.
     *
     * @var string
     */
    protected $base = 'crm/activities';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_activities' ],
                'args'     => $this->get_collection_params(),
            ],
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'create_activity' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_activity' ],
            ],
            [
                'methods'  => WP_REST_Server::EDITABLE,
                'callback' => [ $this, 'update_activity' ],
            ],
            [
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => [ $this, 'delete_activity' ],
            ],
        ] );
    }

    /**
     * Get a collection of activities
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_activities( $request ) {
        $args = [
            'limit'  => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $activities = erp_crm_get_feed_activity( $args );

        // $activities = array_map( function( $activity ) {
        //     $mapper_fields = $this->get_fields( $activity['type'] );

        //     $activity = $this->transformer( $activity, $mapper_fields );

        //     return $activity;
        // }, $activities );

        return new WP_REST_Response( $activities, 200 );
    }

    /**
     * Get a specific activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_activity( $request ) {
        $activity_id   = (int) $request['id'];
        $activity      = erp_crm_customer_get_single_activity_feed( $activity_id );

        $mapper_fields = $this->get_fields( $activity['type'] );
        $activity      = $this->transformer( $activity, $mapper_fields );

        return new WP_REST_Response( $activity, 200 );
    }

    /**
     * Create a activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_activity( $request ) {
        // Task
        $task_title = $request['task_title'];
        $body    = $request['body'];

        $employee_ids = $request['user'];

        $extra_data = [
            'task_title'     => $task_title,
            'invite_contact' => $employee_ids,
        ];

        $post_data = [
            'type'       => 'tasks',
            'message'    => $message,
            'start_date' => date( 'Y-m-d H:i:s', strtotime( $request['task_date'] . $task_title ) ),
            'user_id'    => $request['contact_id'],
            'created_by' => $request['created_by'],
            'extra'      => base64_encode( json_encode( $extra_data ) ),
        ];

        erp_crm_save_customer_feed_data( $post_data );

        // Activity
        $subject = $request['subject'];
        $message = $request['message'];

        $extra_data = [];
        if ( ! empty( $request['invite_contact'] ) ) {
            $extra_data = [
                'invite_contact' => $request['invite_contact'],
            ];
        }

        $post_data = [
            'type'          => 'log_activity',
            'log_type'      => $request['log_type'],
            'message'       => $message,
            'email_subject' => isset( $subject ) ? $subject : '',
            'start_date'    => date( 'Y-m-d H:i:s', strtotime( $request['start_date'] . $request['start_time'] ) ),
            'user_id'       => $request['contact_id'],
            'created_by'    => $request['created_by'],
            'extra'         => base64_encode( json_encode( $extra_data ) ),
        ];

        erp_crm_save_customer_feed_data( $post_data );

        // Meeting
        $schedule_title = $request['schedule_title'];
        $message        = $request['message'];

        $post_data = [
            'schedule_type'              => $request['schedule_type'],
            'schedule_title'             => $schedule_title,
            'message'                    => $message,
            'start_date'                 => $request['start_date'],
            'start_time'                 => $request['start_time'],
            'end_date'                   => $request['end_date'],
            'end_time'                   => $request['end_time'],
            'all_day'                    => $request['all_day'],
            'allow_notification'         => $request['allow_notification'],
            'notification_via'           => $request['notification_via'],
            'notification_time'          => $request['notification_time'],
            'notification_time_interval' => $request['notification_time_interval'],
            'user_id'                    => $request['contact_id'],
            'created_by'                 => $request['created_by'],
            'invite_contact'             => $request['invite_contact'],
        ];

        $save_data = erp_crm_customer_prepare_schedule_postdata( $post_data );
        erp_crm_save_customer_feed_data( $save_data );

        return new WP_REST_Response( true, 200 );
    }

    /**
     * Update a activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_activity( $request ) {
        return new WP_REST_Response( true, 200 );
    }

    /**
     * Delete a activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_activity( $request ) {
        $activity_id = (int) $request['id'];

        erp_crm_customer_delete_activity_feed( $activity_id );

        return new WP_REST_Response( true, 200 );
    }

    public function get_fields( $type = 'note' ) {

        $common_fields = [
            'contact_id' => 'user_id',
            'created_by' => 'created_by',
        ];

        switch ( $type ) {
            case 'note':
                $fields = [
                    'content' => 'message',
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'email':
                $fields = [
                    'title'   => 'email_subject',
                    'content' => 'message',
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'log':
                $fields = [
                    'date'           => 'start_date',
                    'time'           => 'start_time',
                    'content'        => 'message',
                    'invite_contact' => 'invite_contact',
                    'title'          => 'title',
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'schedule':
                $fields = [
                    'title'                      => 'title',
                    'start_date'                 => 'start_date',
                    'start_time'                 => 'start_time',
                    'end_date'                   => 'end_date',
                    'end_time'                   => 'end_time',
                    'all_day'                    => 'all_day',
                    'content'                    => 'content',
                    'invite_contact'             => 'invite_contact',
                    'schedule_type'              => 'schedule_type',
                    'allow_notification'         => 'allow_notification',
                    'notification_via'           => 'notification_via',
                    'notification_time'          => 'notification_time',
                    'notification_time_interval' => 'notification_time_interval',
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'task':
                $fields = [
                    'title'        => 'title',
                    'employee_ids' => 'employee_ids',
                    'date'         => 'date',
                    'time'         => 'time',
                    'content'      => 'content',
                ];

                $fields = array_merge( $common_fields, $fields );
                break;
        }

        return $fields;
    }
}