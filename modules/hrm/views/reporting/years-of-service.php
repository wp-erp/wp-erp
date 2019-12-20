<div class="wrap wperp-hrm-report">
    <h1><?php esc_html_e( 'Years of Service', 'erp' ); ?></h1>

    <?php
        global $wpdb;

        $all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );

        $hire_data = [];
        foreach ( $all_user_id as $user_id ) {

            $employee = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );
            $date     = date_parse_from_format( 'Y-m-d', $employee->hiring_date );
            $month    = $date['month'];
            $day      = $date['day'];

            if ( $month > 0 ) {

                $hire_data[$month][$day][] = [
                    'emp_name'    => $employee->display_name,
                    'emp_id'      => $employee->get_user_id(),
                    'hiring_date' => $employee->hiring_date
                ];
            }
        }

        ksort( $hire_data );
    ?>

    <div class="postbox" style="margin-top: 20px;">

        <div class="inside">
            <div class="main">
            <?php
                foreach ( $hire_data as $month => $data_month ) {

                    $dateObj = DateTime::createFromFormat( '!m', $month );
                    echo '<h3 class="report-month">' . esc_attr( $dateObj->format( 'F' ) ) . '</h3>';
                    echo '<table class="">';

                    ksort( $data_month );

                    foreach ( $data_month as $date => $data_date ) {
                                                echo "<tr>";
                                                $dayobj = DateTime::createFromFormat( '!d', $date );
                        echo '<th>' . esc_attr( $dayobj->format( 'jS' ) ) . '</th>';

                        $count = count( $data_date );
                        $i     = 0;

                        foreach ( $data_date as $single_data ) {
                                                        echo "<td>";
                            $age = date( 'Y', time() ) - date( 'Y', strtotime( $single_data['hiring_date'] ) );

                            if ( $age > 0 ) {
                                echo "<span class='emp_person'>".esc_attr( $single_data['emp_name'] ) . ' ('. esc_html( $age ) .' '. esc_attr( _n('Year', 'Years', $age ) ) .' ) </span>' ;
                            }

                            if ( ++$i != $count ) {
                                 echo ', ';
                            }
                                                        echo "</td>";
                        }

                        echo '</tr>';
                    }
                                    echo "</table>";
                }
            ?>
            </div>
        </div>
    </div>


</div>
