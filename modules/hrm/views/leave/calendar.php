<?php
$filter_active = ( isset( $_GET['department'] ) && $_GET['department'] != '-1' ) || ( isset( $_GET['designation'] ) && $_GET['designation'] != '-1' ) ? $_GET : false;

$leave_requests = erp_hr_get_calendar_leave_events( $filter_active, false, true );
$events = [];

foreach ( $leave_requests as $key => $leave_request ) {
    $events[] = array(
        'id'        => $leave_request->id,
        'title'     => $leave_request->display_name,
        'start'     => $leave_request->start_date,
        'end'       => $leave_request->end_date,
        'url'       => erp_hr_url_single_employee( $leave_request->user_id ),
        'color'     => $leave_request->color,
        'img'       => get_avatar( $leave_request->user_id, 16 )
    );
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
                erp_html_form_input( array(
                    'name'        => 'department',
                    'value'       =>  isset( $_GET['department'] ) ? sanitize_text_field( wp_unslash( $_GET['department'] ) ) : '',
                    'class'       => 'erp-hrm-select2-add-more erp-hr-dept-drop-down',
                    'custom_attr' => array( 'data-id' => 'erp-new-dept' ),
                    'type'        => 'select',
                    'options'     => erp_hr_get_departments_dropdown_raw()
                ) );

                erp_html_form_input( array(
                    'name'        => 'designation',
                    'value'       => isset( $_GET['designation'] ) ? sanitize_text_field( wp_unslash( $_GET['designation'] ) ) : '',
                    'class'       => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'custom_attr' => array( 'data-id' => 'erp-new-designation' ),
                    'type'        => 'select',
                    'options'     => erp_hr_get_designation_dropdown_raw()
                ) );
            ?>
            <input type="submit" class="button" name="erp_leave_calendar_filter" value="<?php esc_html_e( 'Filter', 'erp' ); ?>">
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
            eventLimit: 4, // allow "more" link when too many events
            events: <?php echo json_encode( $events ); ?>,
            eventRender: function( event, element, calEvent ) {
                if( event.img != 'undefined' ) {
                    element.find('.fc-content').find('.fc-title').before( $("<span class=\"fc-event-icons erp-leave-avatar\">"+event.img+"</span>") );
                }
            },
        });
    });

</script>
