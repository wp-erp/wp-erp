<?php
namespace WeDevs\ERP\HRM\PushNotification;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Orchestrates push notifications for WP ERP events.
 *
 * @since 1.0.0
 */
class NotificationHandler {

    /**
     * The push notification provider.
     *
     * @var NotificationInterface
     */
    private $notification;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param NotificationInterface $notification Push notification provider instance.
     */
    public function __construct( NotificationInterface $notification ) {
        $this->notification = $notification;
    }

    /**
     * Notify HR managers when an employee submits a new leave request.
     *
     * @since 1.0.0
     *
     * @param int   $request_id Leave request ID.
     * @param array $request    Leave request data array.
     *
     * @return void
     */
    public function on_leave_request_new( $request_id, $request ) {
        if ( empty( $request_id ) || empty( $request ) ) {
            return;
        }

        $employee_id   = isset( $request['user_id'] ) ? absint( $request['user_id'] ) : 0;
        $employee_name = $this->get_employee_name( $employee_id );

        $title   = __( 'New Leave Request', 'erp' );
        $message = sprintf( __( '%s has submitted a new leave request.', 'erp' ), $employee_name );

        $manager_ids = $this->get_hr_manager_ids();

        if ( empty( $manager_ids ) ) {
            return;
        }

        $this->notification->send(
            $manager_ids,
            $title,
            $message,
            [
                'type'             => 'leave_request_new',
                'leave_request_id' => absint( $request_id ),
            ]
        );
    }

    /**
     * Notify the employee when their leave request is approved.
     *
     * @since 1.0.0
     *
     * @param int   $request_id Leave request ID.
     * @param array $request    Leave request data array.
     *
     * @return void
     */
    public function on_leave_request_approved( $request_id, $request ) {
        if ( empty( $request_id ) || empty( $request ) ) {
            return;
        }

        $employee_id = isset( $request['user_id'] ) ? absint( $request['user_id'] ) : 0;

        if ( ! $employee_id ) {
            return;
        }

        $title   = __( 'Leave Request Approved', 'erp' );
        $message = __( 'Your leave request has been approved.', 'erp' );

        $this->notification->send(
            [ $employee_id ],
            $title,
            $message,
            [
                'type'             => 'leave_request_approved',
                'leave_request_id' => absint( $request_id ),
            ]
        );
    }

    /**
     * Notify the employee when their leave request is rejected.
     *
     * @since 1.0.0
     *
     * @param int   $request_id Leave request ID.
     * @param array $request    Leave request data array.
     *
     * @return void
     */
    public function on_leave_request_rejected( $request_id, $request ) {
        if ( empty( $request_id ) || empty( $request ) ) {
            return;
        }

        $employee_id = isset( $request['user_id'] ) ? absint( $request['user_id'] ) : 0;

        if ( ! $employee_id ) {
            return;
        }

        $title   = __( 'Leave Request Rejected', 'erp' );
        $message = __( 'Your leave request has been rejected.', 'erp' );

        $this->notification->send(
            [ $employee_id ],
            $title,
            $message,
            [
                'type'             => 'leave_request_rejected',
                'leave_request_id' => absint( $request_id ),
            ]
        );
    }

    /**
     * Send a push notification to employees when an announcement is published.
     *
     * @since 1.0.0
     *
     * @param array $employees Array of employee user IDs.
     * @param int   $post_id   Announcement post ID.
     *
     * @return void
     */
    public function on_announcement( $employees, $post_id ) {
        $push_enabled = get_post_meta( $post_id, '_announcement_send_push', true );

        if ( 'on' !== $push_enabled ) {
            return;
        }

        $post = get_post( $post_id );

        if ( empty( $post ) ) {
            return;
        }

        $title   = get_the_title( $post );
        $message = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '...' );

        if ( empty( $employees ) ) {
            return;
        }

        $user_ids = array_map( 'absint', (array) $employees );

        $this->notification->send(
            $user_ids,
            $title,
            $message,
            [
                'type'            => 'announcement',
                'post_id'         => absint( $post_id ),
                'announcement_id' => absint( $post_id ),
            ]
        );
    }

    /**
     * Retrieve the display name for an employee.
     *
     * @since 1.0.0
     *
     * @param int $employee_id WordPress user ID.
     *
     * @return string
     */
    private function get_employee_name( $employee_id ) {
        if ( ! $employee_id ) {
            return '';
        }

        $user = get_userdata( $employee_id );

        return $user ? $user->display_name : '';
    }

    /**
     * Get the WordPress user IDs of all HR managers.
     *
     * @since 1.0.0
     *
     * @return array
     */
    private function get_hr_manager_ids() {
        $managers = get_users( [ 'role__in' => [ 'erp_hr_manager' ] ] );
        $ids      = [];

        foreach ( $managers as $manager ) {
            $ids[] = absint( $manager->ID );
        }

        return array_values( $ids );
    }
}
