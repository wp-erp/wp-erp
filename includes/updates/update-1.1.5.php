<?php
$results = \WeDevs\ERP\HRM\Models\Leave_Holiday::select('id', 'start', 'end')->get();
        
if ( $results ) {
    foreach ( $results as $key => $result ) {
        $date = new \DateTime($result->end);
        $date->modify('+1 day');
        $new_date = $date->format('Y-m-d H:i:s') ;
        \WeDevs\ERP\HRM\Models\Leave_Holiday::where( 'id', '=', $result->id )->update(['end' => $new_date]);
    }
}

