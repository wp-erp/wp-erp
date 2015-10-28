<?php
$leave_requests = erp_hr_get_leave_requests( array( 'number' => -1 ) );
$events = [];

foreach ( $leave_requests as $key => $leave_request ) {
    $events[] = array(
        'id'        => $leave_request->id,
        'title'     => $leave_request->display_name,
        'start'     => $leave_request->start_date,
        'end'       => $leave_request->end_date,
        'url'       => erp_hr_url_single_employee( $leave_request->user_id ),
        'color'     => '#32b1c8',
        'img'       => get_avatar( $leave_request->user_id, 80 )
        //'className' => ($milestone->completed == 1) ? 'milestone competed' : 'milestone'
    );
}
?>

<style>
    .fc-time {
        display:none;
    }
</style>
<div class="wrap erp-hr-calendar-wrap">
    <div id="erp-hr-calendar"></div>
</div>



<script>
console.log(<?php echo json_encode($events); ?>);
    jQuery(document).ready(function($) {
        
        $('#erp-hr-calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            eventLimit: true, // allow "more" link when too many events
            events: <?php echo json_encode($events); ?>,
            eventRender: function(event, element, calEvent) {

                if( event.img != 'undefined' ) {
                    element.find('.fc-content').find('.fc-title').before( $("<span class=\"fc-event-icons\">"+event.img+"</span>") );
                }
            },
        });
    });

</script>