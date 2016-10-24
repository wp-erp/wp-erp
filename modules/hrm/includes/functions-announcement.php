<?php

/**
 * Assign / Send announcements to the employee(s)
 *
 * @param  int     $post_id
 * @param  string  $type
 * @param  array   $employees
 *
 * @return void
 */
function erp_hr_assign_announcements_to_employees( $post_id, $type, $employees = [] ) {
    $post      = get_post( $post_id );
    $post_type = $post->post_status;

    update_post_meta( $post_id, '_announcement_type', $type );
    update_post_meta( $post_id, '_announcement_selected_user', $employees );

    if ( $type == 'all_employee' ) {
        $empls = erp_hr_get_employees( array( 'no_object' => true ) );

        if ( $empls ) {
            foreach ( $empls as $user ) {
                $employees[] = (int) $user->user_id;
            }
        }
    }

    $announces_object = \WeDevs\ERP\HRM\Models\Announcement::where( 'post_id', $post_id )->whereIn( 'user_id', $employees );

    $announcements      = $announces_object->get();
    $existing_employees = array_pluck( $announcements->toArray(), 'user_id' );


    $new_employees = array_diff( $employees, $existing_employees );

    $data = [];
    foreach ( $new_employees as $item ) {
        $data[] = [
            'user_id'      => $item,
            'post_id'      => $post_id,
            'status'       => 'unread',
            'email_status' => ( $post_type == 'publish' ) ? 'sent' : 'not_sent'
        ];
    }

    \WeDevs\ERP\HRM\Models\Announcement::insert( $data );

    // Send email only when the announcement is published & send to not recieved yet employees
    if ( $post_type == 'publish' ) {
        $not_recieved_employees = array_pluck( $announcements->where( 'email_status', 'not_sent' )->toArray(), 'user_id' );
        $recipients             = array_unique( array_merge( $new_employees, $not_recieved_employees ) );
        $employee_chunks        = array_chunk( $recipients, 100 );

        $announces_object->update( ['email_status' => 'sent'] );

        $count = 0;
        foreach ( $employee_chunks as $employee_chunk ) {
            wp_schedule_single_event( time() + ( 300 * $count ), 'erp_hr_schedule_announcement_email', [ $employee_chunk, $post_id ] );

            $count ++;
        }
    }

    do_action( 'hr_announcement_insert_assignment', $new_employees, $post_id );
}

/**
 * Send Announcement Email
 *
 * @param  array $employee_ids
 * @param  int   $post_id
 *
 * @return void
 */
function erp_hr_send_announcement_email( $employee_ids, $post_id ) {
    $announcement_email = new \WeDevs\ERP\HRM\Emails\HR_Announcement_Email();

    $announcement_email->trigger( $employee_ids, $post_id );
}