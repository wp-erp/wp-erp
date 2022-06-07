<?php
$department_id  = isset( $_GET['department'] ) && absint( wp_unslash( $_GET['department'] ) ) != '-1' ? absint( wp_unslash( $_GET['department'] ) ) : 0;
$designation_id = isset( $_GET['designation'] ) && absint( wp_unslash( $_GET['designation'] ) ) != '-1' ? absint( wp_unslash( $_GET['designation'] ) ) : 0;

$args = [
    'status'            => 1,
    'number'            => '-1',
    'department_id'     => $department_id,
    'designation_id'    => $designation_id,
    'year'              => erp_current_datetime()->format( 'Y' ),
];

$leave_requests = erp_hr_get_leave_requests( $args );
$leave_requests = $leave_requests['data'];

$events = [];

foreach ( $leave_requests as $key => $leave_request ) {
    $event_label = $leave_request->display_name;
    // Half day leave
    if ( $leave_request->day_status_id != 1 ) {
        $event_label .= '(' . erp_hr_leave_request_get_day_statuses( $leave_request->day_status_id ) . ')';
    }

    $events[] = [
        'id'        => $leave_request->id,
        'title'     => $event_label,
        'start'     => erp_current_datetime()->setTimestamp( $leave_request->start_date )->setTime( 0, 0, 0 )->format(  'Y-m-d H:i:s' ),
        'end'       => erp_current_datetime()->setTimestamp( $leave_request->end_date )->setTime( 23, 59, 59 )->format( 'Y-m-d H:i:s' ),
        'url'       => erp_hr_url_single_employee( $leave_request->user_id ),
        'color'     => $leave_request->color,
        'img'       => get_avatar( $leave_request->user_id, 16 ),
    ];
}
?>

<style>
    .fc-time {
        display:none;
    }
    .erp-leave-avatar img {
        border-radius: 50%;
        margin: 3px 7px 0 0;

    }
    .erp-calendar-filter {
        margin: 15px 0px;
    }
    .fc-title {
        position: relative;
        top: -4px;
    }
</style>
<div class="wrap erp-hr-calendar-wrap">

    <h1><?php esc_html_e( 'Calendar', 'erp' ); ?></h1>

    <div class="tablenav top erp-calendar-filter">
        <form method="post" action="">
             <?php
                erp_html_form_input( [
                    'name'        => 'department',
                    'value'       => isset( $_GET['department'] ) ? sanitize_text_field( wp_unslash( $_GET['department'] ) ) : '',
                    'class'       => 'erp-hrm-select2-add-more erp-hr-dept-drop-down',
                    'custom_attr' => [ 'data-id' => 'erp-new-dept' ],
                    'type'        => 'select',
                    'options'     => erp_hr_get_departments_dropdown_raw(),
                    'value'       => $department_id,
                ] );

                erp_html_form_input( [
                    'name'        => 'designation',
                    'value'       => isset( $_GET['designation'] ) ? sanitize_text_field( wp_unslash( $_GET['designation'] ) ) : '',
                    'class'       => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'custom_attr' => [ 'data-id' => 'erp-new-designation' ],
                    'type'        => 'select',
                    'options'     => erp_hr_get_designation_dropdown_raw(),
                    'value'       => $designation_id,
                ] );
            ?>
            <input type="submit" class="button" name="erp_leave_calendar_filter" value="<?php esc_attr_e( 'Filter', 'erp' ); ?>">
            <?php wp_nonce_field( 'erp_calendar_filter' ); ?>
        </form>
    </div>

    <div id="erp-hr-calendar"></div>
</div>

<script>
    ;jQuery(document).ready(function($) {
        $('#erp-hr-calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            firstDay: <?php echo esc_attr( get_option( 'start_of_week', 1 ) );?>,
            eventLimit: 4, // allow "more" link when too many events
            events: <?php echo wp_json_encode( $events ); ?>,
            eventRender: function( event, element, calEvent ) {
                if( event.img != 'undefined' ) {
                    element.find('.fc-content').find('.fc-title').before( $("<span class=\"fc-event-icons erp-leave-avatar\">"+event.img+"</span>") );
                }
            },
        });
    });

</script>
