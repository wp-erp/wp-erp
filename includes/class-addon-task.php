<?php
namespace WeDevs\ERP;

use DatePeriod;
use DateTime;
use DateInterval;

class AddonTask {
    public function __construct() {
        $this->action_hook();
    }

    public function action_hook() {
        add_action( 'erp_hr_new_holiday' , [ $this, 'erp_hr_new_holiday_hook_callback' ], 10, 2 );
        add_action( 'erp_hr_after_update_holiday' , [ $this, 'erp_hr_after_update_holiday_hook_callback' ], 10, 2 );
        add_action( 'erp_hr_leave_holiday_delete' , [ $this, 'erp_hr_leave_holiday_delete_hook_callback' ], 10, 1 );
        add_action( 'erp_hr_leave_request_approved' , [ $this, 'erp_hr_leave_request_approved_hook_callback' ], 10, 2 );
        add_action( 'erp_hr_leave_request_pending' , [ $this, 'erp_hr_leave_request_pending_hook_callback' ], 10, 2 );
        add_action( 'erp_hr_leave_request_reject' , [ $this, 'erp_hr_leave_request_reject_hook_callback' ], 10, 2 );
    }

    public function erp_hr_new_holiday_hook_callback( $id, $args ) {
        if ( ! empty( $id ) && ! empty( $args ) ) {
            $this->holiday_create( $id, $args );
        }
    }

    public function erp_hr_after_update_holiday_hook_callback( $id, $args ) {
        if ( ! empty( $id ) && ! empty( $args ) ) {
            $this->holiday_create($id, $args);
        }
    }

    public function erp_hr_leave_holiday_delete_hook_callback( $id ) {
        $result = $this->make_query( 'select', '',[ 'sql' => function( $wpdb ) use ($id) {
            return "SELECT * FROM {$wpdb->prefix}erp_holidays_indv WHERE holiday_id = {$id}";
        } ] );
        $this->make_query( 'delete', 'erp_holidays_indv', [ 'where' => [ 'holiday_id' => $id ] ] );
        do_action( 'after_calling_erp_hr_holiday_delete_hook_callback', $id, $result );
    }

    public function erp_hr_leave_request_approved_hook_callback( $id, $request ) {
        if ( ! empty( $id ) && ! empty( $request ) ) {
            $period = new DatePeriod(
                new DateTime( $request->start_date ),
                new DateInterval( 'P1D' ),
                new DateTime( $request->end_date )
            );
            foreach ( $period as $key => $value ) {
                $leaveday_value = $value->format( 'Y-m-d' );
                $data          = [
                    'user_id'     => $request->user_id,
                    'request_id'  => $id,
                    'title'       => $request->comments,
                    'date'        => $leaveday_value,
                ];
                $result = $this->make_query( 'insert', 'erp_user_leaves', [ 'data' => $data ] );
                do_action( 'after_calling_erp_hr_leave_request_approved_hook_callback', $result, $data, $id, $request);
            }
        }
    }

    public function erp_hr_leave_request_pending_hook_callback( $id, $request ) {
        $results = $this->make_query( 'select', '',[ 'sql' => function( $wpdb ) use ( $id, $request ) {
            return "SELECT * FROM {$wpdb->prefix}erp_user_leaves WHERE user_id = {$request->user_id} AND request_id = {$id}";
        } ] );
        $this->make_query( 'delete', 'erp_user_leaves', [ 'where' => [ 'user_id' => $request->user_id, 'request_id' => $id ] ]);
        do_action( 'after_calling_erp_hr_leave_request_pending_hook_callback', $results, $id, $request);
    }

    public function erp_hr_leave_request_reject_hook_callback( $id, $request ) {
        $results = $this->make_query( 'select', '',[ 'sql' => function( $wpdb ) use ( $id, $request ) {
            return "SELECT * FROM {$wpdb->prefix}erp_user_leaves WHERE user_id = {$request->user_id} AND request_id = {$id}";
        } ] );
        $this->make_query( 'delete', 'erp_user_leaves', [ 'where' => [ 'user_id' => $request->user_id, 'request_id' => $id ] ]);
        do_action( 'after_calling_erp_hr_leave_request_pending_hook_callback', $results, $id, $request);
    }

    public function make_query( $type, $table, $input_data ) {
        global $wpdb;
        $table = $wpdb->prefix . $table;
        if ( $type == 'insert' ) {
            return $wpdb->insert( $table, $input_data['data'] );
        }
        if ( $type == 'update' ) {
            return $wpdb->update( $table, $input_data['data'], $input_data['where'] );
        }
        if ( $type == 'delete' ) {
            return $wpdb->delete( $table, $input_data['where'] );
        }
        if ( $type == 'select' || $type == 'raw' ) {
            return $wpdb->get_results( $input_data['sql']( $wpdb ) );
        }
    }

    public function holiday_create( $id, $args ) {
        if ( ! empty( $id ) && ! empty( $args ) ) {
            $results_prev = $this->make_query( 'select', '',[ 'sql' => function( $wpdb ) use ( $id ) {
                return "SELECT * FROM {$wpdb->prefix}erp_holidays_indv WHERE holiday_id = {$id}";
            } ] );
            $this->make_query('delete', 'erp_holidays_indv', [ 'where' => [ 'holiday_id' => $id ] ]);
            $period = new DatePeriod(
                new DateTime( $args['start'] ),
                new DateInterval( 'P1D' ),
                new DateTime( $args['end'] )
            );
            foreach ( $period as $key => $value ) {
                $holiday_value = $value->format( 'Y-m-d' );
                $data          = [
                    'holiday_id'  => $id,
                    'title'       => $args['title'],
                    'date'        => $holiday_value,
                ];
                $this->make_query( 'insert', 'erp_holidays_indv', [ 'data' => $data ] );
            }
            $results_now = $this->make_query( 'select', '',[ 'sql' => function( $wpdb ) use ( $id ) {
                return "SELECT * FROM {$wpdb->prefix}erp_holidays_indv WHERE holiday_id = {$id}";
            } ] );
            do_action( 'after_calling_erp_hr_holiday_create_hook_callback', $results_prev, $results_now );
        }
    }
}
