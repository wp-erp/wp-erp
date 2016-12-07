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
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'crm/activities';

    public function __construct() {
        $this->activity_types = [
            'note'     => 'new_note',
            'log'      => 'log_activity',
            'schedule' => 'schedule',
            'task'     => 'tasks',
            'email'    => 'email',
        ];
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_activities' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_activites' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_activity' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_activites' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_activity' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_activites' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_activity' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_activites' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_activity' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_activites' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
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
            'type'   => $this->activity_types[ $request['type'] ],
            'limit'  => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $items = erp_crm_get_feed_activity( $args );
        $items = erp_array_to_object( $items );

        $args['count'] = true;
        $total_items   = erp_crm_get_feed_activity( $args );;

        $formated_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_activity( $request ) {
        $activity_id = (int) $request['id'];
        $activity    = (object) erp_crm_customer_get_single_activity_feed( $activity_id );
        $activity    = $this->prepare_item_for_response( $activity, $request );
        $response    = rest_ensure_response( $activity );

        return $response;
    }

    /**
     * Create a activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_activity( $request ) {
        $item = $this->prepare_item_for_database( $request );

        switch ( $item['type'] ) {
            case 'note':
            case 'email':
                $item['type'] = $this->activity_types[ $item['type'] ];
                $saved_data   = erp_crm_save_customer_feed_data( $item );
                break;

            case 'log':
                $extra_data = [
                    'invite_contact' => $item['invited_user']
                ];
                $item['start_date'] = date( 'Y-m-d H:i:s', strtotime( $item['start_date'] ) );
                $item['extra']      = base64_encode( json_encode( $extra_data ) );

                $item['type'] = $this->activity_types[ $item['type'] ];
                $saved_data   = erp_crm_save_customer_feed_data( $item );
                break;

            case 'schedule':
                $item['type'] = $this->activity_types[ $item['type'] ];

                $extra_data = [
                    'schedule_title'     => $item['email_subject'],
                    'all_day'            => isset( $item['all_day'] ) ? (string) $item['all_day'] : 'false',
                    'allow_notification' => isset( $item['allow_notification'] ) ? (string) $item['allow_notification'] : 'false',
                    'invite_contact'     => ! empty( $item['invite_contact'] ) ? $item['invite_contact'] : [],
                ];

                if ( $item['allow_notification'] == 'true' ) {
                    $notify_date = new \DateTime( $item['start_date'] );
                    $notify_date->modify('-' . $item['notification_time_interval'] . ' '. $item['notification_time'] );
                    $extra_data['notification_datetime'] = $notify_date->format( 'Y-m-d H:i:s' );
                } else {
                    $extra_data['notification_datetime'] = '';
                }

                $post_data = [
                    'type'       => 'log_activity',
                    'log_type'   => $item['schedule_type'],
                    'message'    => $item['message'],
                    'start_date' => date( 'Y-m-d H:i:s', strtotime( $item['start_date'] ) ),
                    'end_date'   => date( 'Y-m-d H:i:s', strtotime( $item['end_date'] ) ),
                    'user_id'    => $item['user_id'],
                    'created_by' => $item['created_by'],
                    'extra'      => base64_encode( json_encode( $extra_data ) ),
                ];

                $saved_data = erp_crm_save_customer_feed_data( $post_data );

                break;

            case 'task':
                $extra_data = [
                    'task_title'     => $item['task_title'],
                    'invite_contact' => ! empty( $item['invite_contact'] ) ? $item['invite_contact'] : [],
                ];

                $post_data = [
                    'type'       => 'tasks',
                    'message'    => $item['message'],
                    'start_date' => date( 'Y-m-d H:i:s', strtotime( $item['start_date'] ) ),
                    'user_id'    => $item['user_id'],
                    'created_by' => $item['created_by'],
                    'extra'      => base64_encode( json_encode( $extra_data ) ),
                ];

                $saved_data = erp_crm_save_customer_feed_data( $post_data );

                break;
        }

        $activity = (object) erp_crm_customer_get_single_activity_feed( $saved_data['id'] );
        $response = $this->prepare_item_for_response( $activity, ['id' => $saved_data['id']] );

        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update a activity
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_activity( $request ) {
        return new WP_REST_Response( true, 201 );
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

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [
            'created_by' => get_current_user_id(),
        ];

        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( isset( $request['type'] ) ) {
            $prepared_item['type'] = $request['type'];
        }

        if ( isset( $request['contact_id'] ) ) {
            $prepared_item['user_id'] = absint( $request['contact_id'] );
        }

        switch ( $request['type'] ) {
            case 'note':
                if ( isset( $request['content'] ) ) {
                    $prepared_item['message'] = $request['content'];
                }

                break;

            case 'email':
                if ( isset( $request['subject'] ) ) {
                    $prepared_item['email_subject'] = $request['subject'];
                    $prepared_item['message']       = $request['body'];
                }
                break;

            case 'log':
                if ( isset( $request['date_time'] ) ) {
                    $prepared_item['start_date'] = $request['date_time'];
                }

                if ( isset( $request['content'] ) ) {
                    $prepared_item['message'] = $request['content'];
                }

                if ( isset( $request['log_type'] ) ) {
                    $prepared_item['log_type'] = $request['log_type'];

                    if ( $request['log_type'] == 'meeting' && isset( $request['employee_ids'] ) ) {
                        $prepared_item['invited_user'] = $request['employee_ids'];
                    }

                    if ( $request['log_type'] == 'email' && isset( $request['subject'] ) ) {
                        $prepared_item['email_subject'] = $request['subject'];
                    }
                }
                break;

            case 'schedule':
                if ( isset( $request['title'] ) ) {
                    $prepared_item['email_subject'] = $request['title'];
                }

                if ( isset( $request['start_date_time'] ) ) {
                    $prepared_item['start_date'] = $request['start_date_time'];
                }

                if ( isset( $request['end_date_time'] ) ) {
                    $prepared_item['end_date'] = $request['end_date_time'];
                }

                if ( isset( $request['all_day'] ) ) {
                    $prepared_item['all_day'] = $request['all_day'];
                }

                if ( isset( $request['content'] ) ) {
                    $prepared_item['message'] = $request['content'];
                }

                if ( isset( $request['employee_ids'] ) ) {
                    $prepared_item['invite_contact'] = explode( ",", str_replace( " ", "", $request['employee_ids'] ) );
                }

                if ( isset( $request['schedule_type'] ) ) {
                    $prepared_item['schedule_type'] = $request['schedule_type'];
                }

                if ( isset( $request['allow_notification'] ) ) {
                    $prepared_item['allow_notification'] = $request['allow_notification'];
                }

                if ( isset( $request['notification_via'] ) ) {
                    $prepared_item['notification_via'] = $request['notification_via'];
                }

                if ( isset( $request['notification_time'] ) ) {
                    $prepared_item['notification_time'] = $request['notification_time'];
                }

                if ( isset( $request['notification_time_interval'] ) ) {
                    $prepared_item['notification_time_interval'] = $request['notification_time_interval'];
                }
                break;

            case 'task':
                if ( isset( $request['title'] ) ) {
                    $prepared_item['task_title'] = $request['title'];
                }

                if ( isset( $request['employee_ids'] ) ) {
                    $prepared_item['invite_contact'] = explode( ",", str_replace( " ", "", $request['employee_ids'] ) );
                }

                if ( isset( $request['date_time'] ) ) {
                    $prepared_item['start_date'] = $request['date_time'];
                }

                if ( isset( $request['content'] ) ) {
                    $prepared_item['message'] = $request['content'];
                }
                break;
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $types = array_flip( $this->activity_types );

        // Convert to a standard type
        $item->type = $types[ $item->type ];

        $common_fields = [
            'id'          => (int) $item->id,
            'type'        => $item->type,
            'contact_id'  => (int) $item->user_id,
            'created_by'  => $item->created_by,
        ];

        switch ( $item->type ) {
            case 'note':
                $fields = [
                    'content' => $item->message,
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'email':
                $fields = [
                    'subject' => $item->email_subject,
                    'body'    => $item->message,
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'log':
                $fields = [
                    'date_time' => $item->start_date,
                    'log_type'  => $item->log_type,
                    'content'   => $item->message,
                ];

                if ( $item->log_type == 'meeting' ) {
                    $fields['employee_ids'] = wp_list_pluck( $item->extra['invited_user'], 'id' );
                }

                if ( $item->log_type == 'email' ) {
                    $fields['subject'] = $item->email_subject;
                }

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'schedule':
                $fields = [
                    'title'                 => $item->email_subject,
                    'start_date_time'       => $item->start_date,
                    'end_date_time'         => $item->end_date,
                    'all_day'               => $item->extra['all_day'],
                    'content'               => $item->message,
                    'employee_ids'          => wp_list_pluck( $item->extra['invited_user'], 'id' ),
                    'schedule_type'         => $item->extra['schedule_type'],
                    'allow_notification'    => $item->extra['allow_notification'],
                    'notification_datetime' => $item->extra['notification_datetime'],
                ];

                $fields = array_merge( $common_fields, $fields );
                break;

            case 'task':
                $fields = [
                    'title'        => $item->extra['task_title'],
                    'employee_ids' => wp_list_pluck( $item->extra['invited_user'], 'id' ),
                    'date_time'    => $item->start_date,
                    'content'      => $item->message,
                ];

                $fields = array_merge( $common_fields, $fields );
                break;
        }

        // Wrap the data in a response object
        $response = rest_ensure_response( $fields );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Get the query params for collections.
     *
     * @return array
     */
    public function get_collection_params() {
        return [
            'context'                => $this->get_context_param(),
            'type'                   => [
                'description'        => __( 'The type of activities.' ),
                'type'               => 'string',
                'default'            => 'log',
            ],
            'page'                   => [
                'description'        => __( 'Current page of the collection.' ),
                'type'               => 'integer',
                'default'            => 1,
                'sanitize_callback'  => 'absint',
                'validate_callback'  => 'rest_validate_request_arg',
                'minimum'            => 1,
            ],
            'per_page'               => [
                'description'        => __( 'Maximum number of items to be returned in result set.' ),
                'type'               => 'integer',
                'default'            => 20,
                'minimum'            => 1,
                'maximum'            => 100,
                'sanitize_callback'  => 'absint',
                'validate_callback'  => 'rest_validate_request_arg',
            ],
        ];
    }
}