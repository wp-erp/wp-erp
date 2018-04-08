<?php
/**
 * Get all crm reports
 *
 * @return array
 */
function erp_crm_get_reports() {

    $reports = [
        'activity-report' => [
            'title'       => __( 'Activity Report', 'erp' ),
            'description' => __( 'Activity report for crm', 'erp' )
        ],
        'customer-report' => [
            'title'       => __( 'Customer Report', 'erp' ),
            'description' => __( 'Customer report for crm', 'erp' )
        ],
        'growth-report' => [
            'title'       => __( 'Growth Report', 'erp' ),
            'description' => __( 'Growth report for crm', 'erp' )
        ],
    ];

    return apply_filters( 'erp_crm_reports', $reports );
}

/**
 * Report Activity filter form
 *
 * @since  1.3.6
 *
 * @return void
 */
function erp_crm_activity_report_filter_form($start = true, $end = true) {
    $start = $start ? $start : false;
    $end   = $end   ? $end : false;

    echo '<form class="erp-crm-report-filter-form" action="" method="post">';

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from',
            'placeholder' => __( 'Form', 'erp' ),
            'value'       => isset( $_POST['start'] ) ? $_POST['start'] : ''
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to',
            'placeholder' => __( 'To', 'erp' ),
            'value'       => isset( $_POST['end'] ) ? $_POST['end'] : ''
        ) );
    }

    wp_nonce_field('erp_crm_nonce_report');

    submit_button( __( 'Filter', 'erp' ), 'secondary', 'erp_crm_report_filter', false );

    echo '</form>';
}

/**
 * Report Customer filter form
 *
 * @since  1.3.6
 *
 * @return void
 */
function erp_crm_customer_report_filter_form($start = true, $end = true, $type = false) {
    $start = $start ? $start : false;
    $end   = $end ? $end : false;
    $type  = $type ? $type : 'all';
    echo '<form class="erp-crm-report-filter-form" action="" method="post">';

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from',
            'placeholder' => __( 'Form', 'erp' ),
            'value'       => isset( $_POST['start'] ) ? $_POST['start'] : ''
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to',
            'placeholder' => __( 'To', 'erp' ),
            'value'       => isset( $_POST['end'] ) ? $_POST['end'] : ''
        ) );
    }

    erp_html_form_input( array(
            'name'        => 'filter_type',
            'placeholder' => __( 'Select a type', 'erp' ),
            'type'        => 'select',
            'class'       => 'filter-types',
            'id'          => 'erp-crm-select-types',
            'options'     => [
                'life_stage'    => __( 'All Types', 'erp' ),
                'contact_owner' => __( 'Owner Wise', 'erp' ),
                'country'       => __( 'Country Wise', 'erp' ),
                'source'        => __( 'Source Wise', 'erp' ),
                'group'         => __( 'Group Wise', 'erp' ),
            ],
            'value'       => isset($_POST['filter_type']) ? $_POST['filter_type'] : 'life_stage'
        )
    );

    wp_nonce_field('erp_crm_nonce_report');

    submit_button( __( 'Filter', 'erp' ), 'secondary', 'erp_crm_report_filter', false );

    echo '</form>';
}

/**
 * Report Growth filter form
 *
 * @since  1.3.6
 *
 * @return void
 */
function erp_crm_growth_report_filter_form($start = true, $end = true, $type = false) {
    $start = $start ? $start : false;
    $end   = $end ? $end : false;
    $type  = $type ? $type : 'all';

    echo '<form class="erp-crm-report-filter-form" action="" method="post">';

    erp_html_form_input( array(
            'name'        => 'filter_type',
            'placeholder' => __( 'Select a type', 'erp' ),
            'type'        => 'select',
            'class'       => 'filter-types',
            'id'          => 'crm-filter-duration',
            'options'     => [
                'this_year'    => __( 'This Year',    'erp' ),
                'custom'       => __( 'Custom',       'erp' )
            ],
            'value'       => isset($_POST['filter_type']) ? $_POST['filter_type'] : 'this_year'
        )
    );

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from custom-filter',
            'placeholder' => __( 'From', 'erp' ),
            'value'       => isset( $_POST['start'] ) ? $_POST['start'] : ''
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to custom-filter',
            'placeholder' => __( 'To', 'erp' ),
            'value'       => isset( $_POST['end'] ) ? $_POST['end'] : ''
        ) );
    }

    wp_nonce_field('erp_crm_nonce_report');

    submit_button( __( 'Filter', 'erp' ), 'secondary', 'erp_crm_report_filter', false );

    echo '</form>';
}

/**
 * Activities report query
 *
 * @param  string $start
 * @param  string $end
 *
 * @since  1.3.6
 *
 * @return array
 */
function erp_crm_activity_reporting_query( $start_date, $end_date ) {
    $activities = \WeDevs\ERP\CRM\Models\Activity::select( 'type', \WeDevs\ORM\Eloquent\Facades\DB::raw( 'count(*) as total' ) );

    if ( $start_date ) {
        $activities->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created_at' ), array( $start_date, $end_date ) );
    }

    return $activities->groupBy( 'type' )->orderBy( 'total', 'desc' )->get();
}

/**
 * Customer report query helper
 *
 * @param  string $filter_type
 *
 * @since  1.3.6
 *
 * @return array
 */

function customer_report_query_helper( $filter_type, $id ) {
    return \WeDevs\ERP\Framework\Models\People
        ::select( 'life_stage', \WeDevs\ORM\Eloquent\Facades\DB::raw( 'count(*) as num' ) )
        ->where( $filter_type, $id )
        ->whereNotNull( 'life_stage' )
        ->groupBy( 'life_stage' )
        ->orderBy( 'num', 'desc' )->get();
}

/**
 * Customer report query
 *
 * @param  string $start
 * @param  string $end
 * @param  string $filter_type
 *
 * @since  1.3.6
 *
 * @return array
 */
function erp_crm_customer_reporting_query( $start_date, $end_date, $filter_type ) {

    switch ( $filter_type ) {

        case 'source':
            $results = \WeDevs\ERP\Framework\Models\People::whereNotNull('life_stage')->with('meta');

            if ( $start_date ) {
                $results->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created' ), array( $start_date, $end_date ) );
            }

            $results = $results->get();

            $std_obj_arr = [];
            
            foreach ( $results as $result ) {
                if ( ! $result->meta->isEmpty() ) {
                    $std = new \stdClass();
            
                    $std->life_stage = $result->life_stage;
                    foreach ($result->meta as $meta) {
                        if ( $meta->meta_key === 'source' ) {
                            $std->meta_value = $meta->meta_value;
                        }
                    }
            
                    array_push( $std_obj_arr, $std);
                }
            }
        
            $sources    = erp_crm_contact_sources();
            $temp_array = [];
            $reports    = [];
            
            foreach ( $std_obj_arr as $result ) {
                $temp_array[ $result->meta_value ][] = $result->life_stage;
            }
            
            foreach ( $sources as $key => $value) {
                if ( isset( $temp_array[$key] ) ) {
                    $reports[$value] = array_count_values( $temp_array[$key] );
                }
            }

            return $reports;

        case 'group':
            global $wpdb;

            $where = '';

            if ( $start_date ) {
                $where = " WHERE p.created between '{$start_date}' and '{$end_date}'";
            }

            $results = $wpdb->get_results( "SELECT cg.name, p.life_stage FROM `{$wpdb->prefix}erp_crm_contact_subscriber` cs LEFT JOIN `{$wpdb->prefix}erp_peoples` p ON cs.user_id = p.id LEFT JOIN `{$wpdb->prefix}erp_crm_contact_group` cg ON cs.group_id = cg.id {$where}", OBJECT );

            $temp_array = [];
            $reports    = [];
            
            foreach ( $results as $result ) {
                $temp_array[ $result->name ][] = $result->life_stage;
            }

            foreach ( $temp_array as $key => $value) {
                $reports[$key] = array_count_values( $value );
            }
            
            return $reports;

    } // end switch

    $results = \WeDevs\ERP\Framework\Models\People::select( $filter_type, \WeDevs\ORM\Eloquent\Facades\DB::raw( 'count(*) as total' ) );

    if ( $start_date ) {
        $results->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created' ), array( $start_date, $end_date ) );
    }

    $results = $results->groupBy( $filter_type )->whereNotNull( 'life_stage' )->orderBy( 'total', 'DESC' )->get();

    switch ( $filter_type ) {

        case 'contact_owner':
            foreach ( $results as $key => $people ) {
                $id = $people->contact_owner;

                if ( $id ) {
                    $contact = get_user_by( 'id', $people->contact_owner );
                    $results[$key]->contact_owner = $contact->display_name;

                    $results[$key]->owner_data = customer_report_query_helper( $filter_type, $id );
                }
            }

            break;

        case 'country':
            foreach ( $results as $key => $result ) {
                $id = $result->country;

                if ( $id ) {
                    $results[$key]->country_data = customer_report_query_helper( $filter_type, $id );
                }
            }

    } // end switch

    return $results;
}


/**
 * Growth report query
 *
 * @param  string $start
 * @param  string $end
 * @param  string $type
 *
 * @since  1.3.6
 *
 * @return array
 */
function erp_crm_growth_reporting_query( $start_date, $end_date, $type ) {
    $temp_array = [];
    $reports    = [];

    $peoples = \WeDevs\ERP\Framework\Models\People::select( ['life_stage', 'created'] );

    // filter
    if ( 'this_year' == $type ) :
        $pattern = 'F';

        $results = $peoples->whereRaw( 'year(`created`) = ?', array(date('Y')) )
            ->whereNotNull('life_stage')->orderBy('created', 'ASC')->get();
    elseif ( 'custom' == $type ) :
        $pattern = 'd-M-Y';

        $results = $peoples->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created' ), array( $start_date, $end_date ) )
            ->whereNotNull('life_stage')->orderBy('created', 'ASC')->get();
    endif;

    foreach ( $results as $result ) {
        $date = new DateTime( $result->created );
        $temp_array[ $date->format( $pattern ) ][] = $result->life_stage;
    }

    foreach ( $temp_array as $key => $value) {
        $reports[$key] = array_count_values( $value );
    }

    wp_localize_script( 'erp-crm-report', 'growthReport', [
        'type'    => $type,
        'reports' => $reports
    ] );

    return $reports;
}

