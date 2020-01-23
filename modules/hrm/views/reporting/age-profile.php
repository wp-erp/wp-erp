<div class="wrap">
    <h2><?php esc_html_e( 'Age Profile', 'erp' ); ?></h2>

    <?php
        $employees    = new \WeDevs\ERP\HRM\Models\Employee();
        $emp_all      = $employees->where( 'status', 'active' )->get();
        $emp_all_data = get_employee_breakdown_by_age( $emp_all );
        $departments  = erp_hr_get_departments();
        $index        = 0;
        $yaxis_ticks  = [];
        $emp_data     = erp_hr_get_age_breakdown_data();

        foreach ( $departments as $department ) {

            array_push( $yaxis_ticks, [$index, $department->title]);

            $emp_by_dept      = $employees->where( array( 'department' => $department->id, 'status' => 'active' ) )->get();
            $emp_by_dept_data = get_employee_breakdown_by_age( $emp_by_dept );

            $_under18[]       = [$emp_by_dept_data['_under_18'], $index];
            $_18_to_25[]      = [$emp_by_dept_data['_18_to_25'], $index];
            $_26_to_35[]      = [$emp_by_dept_data['_26_to_35'], $index];
            $_36_to_45[]      = [$emp_by_dept_data['_36_to_45'], $index];
            $_46_to_55[]      = [$emp_by_dept_data['_46_to_55'], $index];
            $_56_to_65[]      = [$emp_by_dept_data['_56_to_65'], $index];
            $_65_plus[]        = [$emp_by_dept_data['_65_plus'], $index];

            $index++;
        }

    ?>
    <div class="erp-single-container">
        <div class="erp-area-left" id="poststuff">
        <?php
            echo wp_kses_post( erp_admin_dash_metabox( __( '<i class="fa fa-bar-chart"></i> Employee Age Breakdown Chart', 'erp' ), function() {
        ?>
            <div id="emp-age-breakdown-chart" class="erp-report-chart"></div>
        <?php
            } ) );

            echo wp_kses_post( erp_admin_dash_metabox( __( '<i class="fa fa-bar-chart-o"></i> Employee Age Breakdown by Department', 'erp' ), function() {
        ?>
            <div id="emp-age-breakdown-by-department" class="erp-report-chart"></div>
        <?php
            } ) );
        ?>
        </div>
    </div>

        <table class="widefat striped erp-report-chart">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Under 18 year', 'erp' ); ?></th>
                    <th><?php esc_html_e( '18 to 25 year', 'erp' ); ?></th>
                    <th><?php esc_html_e( '26 to 35 year', 'erp' ); ?></th>
                    <th><?php esc_html_e( '36 to 45 year', 'erp' ); ?></th>
                    <th><?php esc_html_e( '46 to 55 year', 'erp' ); ?></th>
                    <th><?php esc_html_e( '56 to 65 year', 'erp' ); ?></th>
                    <th><?php esc_html_e( '65+ year', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                foreach ( $emp_data as $emp ) {
                    echo '<tr>';
                    echo '<td>' . esc_attr( $emp->department ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_under18 ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_18_to_25 ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_26_to_35 ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_36_to_45 ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_46_to_55 ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_56_to_65 ) . '</td>';
                    echo '<td>' . esc_attr( $emp->_65_plus ) . '</td>';
                    echo '</tr>';
                }
            ?>
            </tbody>
        </table>

    <script type="text/javascript">

        (function($){

            var ageBreakdown =  [
                [0, <?php echo esc_attr( $emp_all_data['_under_18'] ); ?>],
                [1, <?php echo esc_attr( $emp_all_data['_18_to_25'] ); ?>],
                [2, <?php echo esc_attr( $emp_all_data['_26_to_35'] ); ?>],
                [3, <?php echo esc_attr( $emp_all_data['_36_to_45'] ); ?>],
                [4, <?php echo esc_attr( $emp_all_data['_46_to_55'] ); ?>],
                [5, <?php echo esc_attr( $emp_all_data['_56_to_65'] ); ?>],
                [6, <?php echo esc_attr( $emp_all_data['_65_plus'] ); ?>]
            ] ;

            var dataset = [{ label: "<?php esc_html_e( 'Employee by age', 'erp' ); ?>", data: ageBreakdown }];

            $(document).ready(function () {
                $.plot($("#emp-age-breakdown-chart"), dataset, {
                    legend: {
                        show: false
                    },
                    xaxis: {
                        axisLabel: "Age Categories",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 14,
                        axisLabelFontFamily: 'Verdana, Arial',
                        axisLabelPadding: 10,
                        tickLength:0,
                            ticks: [
                                [0, "Under 18"],
                                [1, "18-25"],
                                [2, "26-35"],
                                [3, "36-45"],
                                [4, "46-55"],
                                [5, "56-65"],
                                [6, "65+"]
                            ],
                    },
                    yaxis: {
                        axisLabel: "Employee Count",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 14,
                        axisLabelFontFamily: 'Verdana, Arial',
                        axisLabelPadding: 3,
                        minTickSize : 1,
                        tickDecimals: 0
                    },
                    series: {
                      bars: {
                        show: true,
                        barWidth: .8,
                        fill: 1,
                        color: '#8BA958'
                      }
                    },
                    bars: {
                        align: 'center',
                        fillColor: '#32CD32',
                        lineWidth: 0
                    },
                    grid: {
                        hoverable: true,
                        borderWidth: {
                            left: 2,
                            bottom: 2,
                            top: 2,
                            right: 2
                        },
                        borderColor: '#000',
                        backgroundColor: { colors: ["#ffffff", "#EDF5FF"] }
                    },
                });
                $("#emp-age-breakdown-chart").UseTooltip1();
            });

            var previousPoint = null, previousLabel = null;

            $.fn.UseTooltip1 = function () {
                $(this).bind("plothover", function (event, pos, item) {
                    if (item) {
                        if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                            previousPoint = item.dataIndex;
                            previousLabel = item.series.label;
                            $("#tooltip").remove();

                            var x = item.datapoint[0];
                            var y = item.datapoint[1];

                            var color = item.series.color;

                            //console.log(item.series.xaxis.ticks[x].label);

                            showTooltip(item.pageX,
                            item.pageY,
                            color,
                            "<?php esc_html_e(' Age :', 'erp' ); ?><strong>" + item.series.xaxis.ticks[x].label + "  yr</strong><br><?php esc_html_e( 'Employee :', 'erp' ); ?> <strong>" + y + "</strong>");
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });
            };

            function showTooltip(x, y, color, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    position: 'absolute',
                    display: 'none',
                    top: y - 40,
                    left: x - 120,
                    border: '2px solid ' + color,
                    padding: '3px',
                    'font-size': '9px',
                    'border-radius': '5px',
                    'background-color': '#fff',
                    'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                    opacity: 0.9
                }).appendTo("body").fadeIn(200);
            }
        })(jQuery);


        (function($){
            var ageBreakdownDepartment = [
                {label: 'Under 18', color:'#3e6d98', data: <?php echo json_encode( $_under18 ); ?>},
                {label: '18-25', color:'#648d9e', data: <?php echo json_encode( $_18_to_25 ); ?>},
                {label: '26-35', color:'#D797AF', data: <?php echo json_encode( $_26_to_35 ); ?>},
                {label: '36-45', color:'#DFABBF', data: <?php echo json_encode( $_36_to_45 ); ?>},
                {label: '46-55', color:'#AAC6D4', data: <?php echo json_encode( $_46_to_55 ); ?>},
                {label: '56-65', color:'#AAC6D4', data: <?php echo json_encode( $_56_to_65 ); ?>},
                {label: '65+', color:'#5E606E', data: <?php echo json_encode( $_65_plus ); ?>}
            ];

            $(document).ready(function() {
                $.plot($("#emp-age-breakdown-by-department"), ageBreakdownDepartment, {
                    xaxis: {
                        tickSize: 1,
                        axisLabel: "Employee Count",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 14,
                        axisLabelFontFamily: 'Verdana, Arial',
                        axisLabelPadding: 10
                    },
                    series: {
                        stack: 1,
                        bars: {
                            show: true,
                            barWidth: 0.4,
                            fill:1,
                            lineWidth: 0,
                            min: 0,
                            horizontal: 1
                        }
                    },
                    bars: {
                        align: 'center'
                    },
                    yaxis: {
                        tickLength: 0,
                        ticks: <?php echo json_encode( $yaxis_ticks ); ?>,
                        axisLabel: "Departments",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 14,
                        axisLabelFontFamily: 'Verdana, Arial',
                        axisLabelPadding: 10,
                    },
                    grid: {
                      hoverable: true,
                      clickable: true,
                      borderColor: '#000',
                      borderWidth: {
                        left: 2,
                        bottom: 2,
                        right: 0,
                        top: 0,
                      },
                      backgroundColor: { colors: ["#ffffff", "#F9FBFC"] }
                    }
                });

                $("#emp-age-breakdown-by-department").UseTooltip2();
            });


            var previousPoint = null, previousLabel = null;

            $.fn.UseTooltip2 = function () {
                $(this).bind("plothover", function (event, pos, item) {
                    if (item) {
                        console.log(item);
                        if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                            previousPoint = item.dataIndex;
                            previousLabel = item.series.label;
                            $("#tooltip").remove();

                            var x = item.datapoint[0];
                            var y = item.datapoint[0] - item.datapoint[2];

                            var color = item.series.color;

                            //console.log(item.series.xaxis.ticks[x].label);

                            showTooltip(item.pageX,
                            item.pageY,
                            color,
                            "<strong>" + item.series.yaxis.ticks[item.dataIndex].label + "</strong><br><?php esc_html_e( 'Age : ', 'erp' ); ?><strong>" + item.series.label + " yr </strong><br><?php esc_html_e( 'Employee :', 'erp' ); ?> <strong>" + y + "</strong>");
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });
            };

            function showTooltip(x, y, color, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    position: 'absolute',
                    display: 'none',
                    top: y - 40,
                    left: x - 120,
                    border: '2px solid ' + color,
                    padding: '3px',
                    'font-size': '9px',
                    'border-radius': '5px',
                    'background-color': '#fff',
                    'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                    opacity: 0.9
                }).appendTo("body").fadeIn(200);
            }


         })(jQuery);

    </script>

</div>
