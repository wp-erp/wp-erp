<?php
/**
 * Get all crm reports
 *
 * @return array
 */
function erp_crm_get_reports() {

    $reports = [
        'activity-report' => [
            'title'       => esc_html__( 'Activity Report', 'erp' ),
            'description' => esc_html__( 'Activity report for crm', 'erp' )
        ],
        'customer-report' => [
            'title'       => esc_html__( 'Customer Report', 'erp' ),
            'description' => esc_html__( 'Customer report for crm', 'erp' )
        ],
        'growth-report'   => [
            'title'       => esc_html__( 'Growth Report', 'erp' ),
            'description' => esc_html__( 'Growth report for crm', 'erp' )
        ],
    ];

    return apply_filters( 'erp_crm_reports', $reports );
}

/**
 * Report Activity filter form
 *
 * @return void
 * @since  1.3.6
 *
 */
function erp_crm_activity_report_filter_form( $start = true, $end = true ) {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp_crm_nonce_report' ) ) {
        return;
    }

    $start = $start ? $start : false;
    $end   = $end ? $end : false;

    echo '<form class="erp-crm-report-filter-form" action="" method="post">';

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from',
            'placeholder' => esc_html__( 'Form', 'erp' ),
            'value'       => isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : ''
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to',
            'placeholder' => esc_html__( 'To', 'erp' ),
            'value'       => isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : ''
        ) );
    }

    wp_nonce_field( 'erp_crm_nonce_report' );

    submit_button( esc_html__( 'Filter', 'erp' ), 'secondary', 'erp_crm_report_filter', false );

    echo '</form>';
}

/**
 * Report Customer filter form
 *
 * @return void
 * @since  1.3.6
 *
 */
function erp_crm_customer_report_filter_form( $start = true, $end = true, $type = false ) {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp_crm_nonce_report' ) ) {
        return;
    }

    $start = $start ? $start : false;
    $end   = $end ? $end : false;
    $type  = $type ? $type : 'all';
    echo '<form class="erp-crm-report-filter-form" action="" method="post">';

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from',
            'placeholder' => esc_html__( 'Form', 'erp' ),
            'value'       => isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ): ''
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to',
            'placeholder' => esc_html__( 'To', 'erp' ),
            'value'       => isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ): ''
        ) );
    }

    erp_html_form_input( array(
            'name'        => 'filter_type',
            'placeholder' => esc_html__( 'Select a type', 'erp' ),
            'type'        => 'select',
            'class'       => 'filter-types',
            'id'          => 'erp-crm-select-types',
            'options'     => [
                'life_stage'    => esc_html__( 'All Types', 'erp' ),
                'contact_owner' => esc_html__( 'Owner Wise', 'erp' ),
                'country'       => esc_html__( 'Country Wise', 'erp' ),
                'source'        => esc_html__( 'Source Wise', 'erp' ),
                'group'         => esc_html__( 'Group Wise', 'erp' ),
            ],
            'value'       => isset( $_POST['filter_type'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_type'] ) ): 'life_stage'
        )
    );

    wp_nonce_field( 'erp_crm_nonce_report' );

    submit_button( esc_html__( 'Filter', 'erp' ), 'secondary', 'erp_crm_report_filter', false );

    echo '</form>';
}

/**
 * Report Growth filter form
 *
 * @return void
 * @since  1.3.6
 *
 */
function erp_crm_growth_report_filter_form( $start = true, $end = true, $type = false ) {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp_crm_nonce_report' ) ) {
        return;
    }

    $start = $start ? $start : false;
    $end   = $end ? $end : false;
    $type  = $type ? $type : 'all';

    echo '<form class="erp-crm-report-filter-form" action="" method="post">';

    erp_html_form_input( array(
            'name'        => 'filter_type',
            'placeholder' => esc_html__( 'Select a type', 'erp' ),
            'type'        => 'select',
            'class'       => 'filter-types',
            'id'          => 'crm-filter-duration',
            'options'     => [
                'this_year' => esc_html__( 'This Year', 'erp' ),
                'custom'    => esc_html__( 'Custom', 'erp' )
            ],
            'value'       => isset( $_POST['filter_type'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_type'] ) ) : 'this_year'
        )
    );

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from custom-filter',
            'placeholder' => esc_html__( 'From', 'erp' ),
            'value'       => isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : ''
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to custom-filter',
            'placeholder' => esc_html__( 'To', 'erp' ),
            'value'       => isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ): ''
        ) );
    }

    wp_nonce_field( 'erp_crm_nonce_report' );

    submit_button( esc_html__( 'Filter', 'erp' ), 'secondary', 'erp_crm_report_filter', false );

    echo '</form>';
}

/**
 * Activities report query
 *
 * @param string $start
 * @param string $end
 *
 * @return array
 * @since  1.3.6
 *
 */
function erp_crm_activity_reporting_query( $start_date, $end_date ) {
    $activities = \WeDevs\ERP\CRM\Models\Activity::select( 'type', \WeDevs\ORM\Eloquent\Facades\DB::raw( 'count(*) as total' ) );

    if ( $start_date ) {
        $activities->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created_at' ), array(
            $start_date,
            $end_date
        ) );
    }

    return $activities->groupBy( 'type' )->orderBy( 'total', 'desc' )->get();
}

/**
 * Customer report query helper
 *
 * @param string $filter_type
 *
 * @return array
 * @since  1.3.6
 *
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
 * @param string $start
 * @param string $end
 * @param string $filter_type
 *
 * @return array
 * @since  1.3.6
 *
 */
function erp_crm_customer_reporting_query( $start_date, $end_date, $filter_type ) {

    switch ( $filter_type ) {

        case 'source':
            $results = \WeDevs\ERP\Framework\Models\People::whereNotNull( 'life_stage' )->with( 'meta' );

            if ( $start_date ) {
                $results->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created' ), array(
                    $start_date,
                    $end_date
                ) );
            }

            $results = $results->get();

            $std_obj_arr = [];

            foreach ( $results as $result ) {
                if ( ! $result->meta->isEmpty() ) {
                    $std = new \stdClass();

                    $std->life_stage = $result->life_stage;
                    foreach ( $result->meta as $meta ) {
                        if ( $meta->meta_key === 'source' ) {
                            $std->meta_value = $meta->meta_value;
                        }
                    }

                    array_push( $std_obj_arr, $std );
                }
            }

            $sources    = erp_crm_contact_sources();
            $temp_array = [];
            $reports    = [];

            foreach ( $std_obj_arr as $result ) {
                $temp_array[ $result->meta_value ][] = $result->life_stage;
            }

            foreach ( $sources as $key => $value ) {
                if ( isset( $temp_array[ $key ] ) ) {
                    $reports[ $value ] = array_count_values( $temp_array[ $key ] );
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

            foreach ( $temp_array as $key => $value ) {
                $reports[ $key ] = array_count_values( $value );
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
                    $contact                        = get_user_by( 'id', $people->contact_owner );
                    $results[ $key ]->contact_owner = $contact->display_name;

                    $results[ $key ]->owner_data = customer_report_query_helper( $filter_type, $id );
                }
            }

            break;

        case 'country':
            foreach ( $results as $key => $result ) {
                $id = $result->country;

                if ( $id ) {
                    $results[ $key ]->country_data = customer_report_query_helper( $filter_type, $id );
                }
            }

    } // end switch

    return $results;
}


/**
 * Growth report query
 *
 * @param string $start
 * @param string $end
 * @param string $type
 *
 * @return array
 * @since  1.3.6
 *
 */
function erp_crm_growth_reporting_query( $start_date, $end_date, $type ) {
    $temp_array = [];
    $reports    = [];

    $peoples = \WeDevs\ERP\Framework\Models\People::select( [ 'life_stage', 'created' ] );

    // filter
    if ( 'this_year' == $type ) :
        $pattern = 'F';

        $results = $peoples->whereRaw( 'year(`created`) = ?', array( date( 'Y' ) ) )
                           ->whereNotNull( 'life_stage' )->orderBy( 'created', 'ASC' )->get();
    elseif ( 'custom' == $type ) :
        $pattern = 'd-M-Y';

        $results = $peoples->whereBetween( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'created' ), array(
            $start_date,
            $end_date
        ) )
                           ->whereNotNull( 'life_stage' )->orderBy( 'created', 'ASC' )->get();
    endif;

    foreach ( $results as $result ) {
        $date                                      = new DateTime( $result->created );
        $temp_array[ $date->format( $pattern ) ][] = $result->life_stage;
    }

    foreach ( $temp_array as $key => $value ) {
        $reports[ $key ] = array_count_values( $value );
    }

    wp_localize_script( 'erp-crm-report', 'growthReport', [
        'type'    => $type,
        'reports' => $reports
    ] );

    return $reports;
}

