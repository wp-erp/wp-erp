<div class="wrap">
    <h3><?php _e( 'Headcount', 'erp' ); ?></h3>

    <?php


    ?>

    <?php
        global $wpdb;

        $company_starts  = erp_get_option( 'gen_com_start', 'erp_settings_general' );
        $start_year      = $company_starts ? date( 'Y', strtotime( $company_starts ) ) : current_time( 'Y' );
        $current_year    = current_time( 'Y' );
        $dept_raw        = erp_hr_get_departments_dropdown_raw();
        $query_dept      = isset( $_REQUEST['department'] ) && '-1' != $_REQUEST['department'] ? intval( $_REQUEST['department'] ) : '';
        $query_year      = isset( $_REQUEST['year'] ) && '-1' != $_REQUEST['year'] ? intval( $_REQUEST['year'] ) : '';
        $query           = "SELECT user_id, department, hiring_date, termination_date FROM {$wpdb->prefix}erp_hr_employees";
        $user_all        = $wpdb->get_results( $query );
        $user_filtered   = [];
        $this_month      = $query_year ? date( $query_year . '-12-01') : current_time( 'Y-m-01' );
        $js_this_month   = strtotime( $this_month ) * 1000 + ( 15*24*60*60*1000 );
        $js_year_before  = strtotime( '-12 month', strtotime( $this_month ) ) * 1000 + ( 15*24*60*60*1000 );
        $total_emp_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );

        for ( $i = 0; $i <= 11; $i++ ) {

            $month        = date( "Y-m", strtotime( $this_month ." -$i months" ) );
            $js_month     = strtotime( $month. '-01' ) * 1000;
            $count        = erp_hr_get_headcount( $month, $query_dept, 'month' );
            $chart_data[] = [$js_month, $count];
        }

        $chart_data = [$chart_data];

        foreach ( $user_all as $user ) {

            if ( $query_dept && $user->department != $query_dept ) {
                continue;
            }

            if ( '0000-00-00' == $user->hiring_date ){
                continue;
            }
            $hiring_year      = intval( substr( $user->hiring_date, 0, 4 ) );
            $termination_year = '0000-00-00' == $user->termination_date ? current_time( 'Y' ) : intval( substr( $user->termination_date, 0, 4 ) );

            if ( $query_year ) {
                if ( $query_year < $hiring_year || $query_year > $termination_year ) {
                    continue;
                }
            }

            $user_filtered[] = $user->user_id;
        }

    ?>
    <div class="hr-headcount">
        <form method="get">
            <input type="hidden" name="page" value="erp-hr-reporting">
            <input type="hidden" name="type" value="headcount">
            <select name="year">
            <?php
                echo '<option value="-1">-Select Year-</option>';
                for ( $i = $current_year; $i >= $start_year; $i-- ) {
                    echo '<option value"' . $i . '"' . selected( $query_year, $i ) . '>' . $i . '</option>';
                }
            ?>
            </select>
            <span class="dashicons dashicons-calendar-alt"></span>&nbsp;
            <select name="department">
            <?php
                foreach ( $dept_raw as $key => $dept ) {
                    echo '<option value="' . $key . '"' . selected( $query_dept, $key ) . '>' . $dept . '</option>';
                }
            ?>
            </select>
            <?php wp_nonce_field( 'epr-rep-headcount' ); ?>
            <button type="submit" class="button-secondary" name="filter_headcount"><?php _e( 'Filter', 'erp' ); ?></button>
        </form>
    </div>

    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-2">

            <!-- main content -->
            <div id="post-body-content">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox">

                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->

                        <h2 class="hndle"><span><?php _e( 'Headcount by Month', 'erp' ); ?></span>
                        </h2>

                        <div class="inside">

                            <div id="emp-headcount" style="width:100%;height:400px;"></div>

                        </div>
                        <!-- .inside -->

                    </div>
                    <!-- .postbox -->

                </div>
                <!-- .meta-box-sortables .ui-sortable -->

            </div>
            <!-- post-body-content -->

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">

                <div class="meta-box-sortables">

                    <div class="postbox">

                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->

                        <h2 class="hndle"><span><?php echo current_time( 'M j, Y' ); ?></span></h2>

                        <div class="inside">
                            <span class="dashicons dashicons-groups"></span>
                            <span></span><br>
                            <h4><?php _e( 'Total Employees', 'erp' ); ?> : <?php echo esc_attr( $total_emp_count ); ?></h4>
                        </div>
                        <!-- .inside -->

                    </div>
                    <!-- .postbox -->

                </div>
                <!-- .meta-box-sortables -->

            </div>
            <!-- #postbox-container-1 .postbox-container -->

        </div>
        <!-- #post-body .metabox-holder .columns-2 -->

        <br class="clear">
    </div>
    <!-- #poststuff -->




    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php _e( 'Name', 'erp'); ?></th>
                <th><?php _e( 'Hire Date', 'erp'); ?></th>
                <th><?php _e( 'Job Title', 'erp'); ?></th>
                <th><?php _e( 'Department', 'erp'); ?></th>
                <th><?php _e( 'Location', 'erp'); ?></th>
                <th><?php _e( 'Status', 'erp'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ( $user_filtered as $user_id ) {

                    $employee     = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );
                    $employee_url = '<a href="' . admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $employee->id ) . '">' . $employee->display_name . '</a>';
                    $date_format  = get_option( 'date_format' );
            ?>
                    <tr>
                        <td><?php echo wp_kses_post( $employee_url ); ?></td>
                        <td><?php echo date( $date_format, strtotime( esc_attr( $employee->hiring_date ) ) ); ?></td>
                        <td><?php echo esc_attr( $employee->designation_title ); ?></td>
                        <td><?php echo esc_attr( $employee->department_title ); ?></td>
                        <td><?php echo esc_attr( $employee->location_name ); ?></td>
                        <td><?php echo esc_attr( $employee->status ); ?></td>
                    </tr>
            <?php
                }
             ?>
        </tbody>
    </table>
</div>

<script>
    ;
    (function($){

      $(document).ready(function() {
        $.plot($("#emp-headcount"), <?php esc_attr_e( json_encode( $chart_data ) ); ?>, {
            xaxis: {
              mode: 'time',
              tickLength: 0,
              tickSize: [1, 'month'],
              min: <?php echo $js_year_before; ?>,
              max: <?php echo $js_this_month; ?>,
              axisLabel: "Headcount by Month",
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 14,
            axisLabelFontFamily: 'Verdana, Arial',
            axisLabelPadding: 10
            },
            yaxis: {
              show: false
            },
            series: {
              bars: {
                show: true,
                fill: 1,
                color: '#8BA958',
                barWidth: 20*24*60*60*1000
              },
              valueLabels: {
                show: true,
                font: "9pt 'Trebuchet MS'",
                align: 'center'
              }
            },
            bars: {
                align: 'center',
                fillColor: '#32CD32',
                lineWidth: 0
            },
            grid: {
                hoverable: true,
                clickable: true,
                borderWidth: 0
            }
          });
      });

    })(jQuery);

</script>
