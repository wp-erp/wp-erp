<div class="wrap erp crm-dashboard">
    <h2><?php _e( 'CRM Dashboard', 'erp' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

                <div class="erp-grid-container badge-container">
                    <?php
                        $contacts_count  = erp_crm_customer_get_status_count( 'contact' );
                        $companies_count = erp_crm_customer_get_status_count( 'company' );
                    ?>
                    <div class="row">
                        <div class="col-3 badge-wrap">
                            <div class="row">
                                <div class="badge-inner total-counter col-2">
                                    <h3><?php echo number_format_i18n( $contacts_count['all']['count'], 0 ); ?></h3>
                                    <p>
                                        <?php echo sprintf( _n( 'Contact', 'Contacts', $contacts_count['all']['count'], 'erp' ), number_format_i18n( $companies_count['all']['count'] ), 0 ); ?>
                                    </p>
                                </div>

                                <div class="badge-inner col-4">
                                    <ul class="erp-dashboard-total-counter-list">
                                        <?php
                                        foreach ( $contacts_count as $contact_key => $contact_value ) {
                                            if ( $contact_key == 'all' ) {
                                                continue;
                                            }
                                            ?>
                                            <li>
                                                <a href="<?php echo add_query_arg( [ 'page' => 'erp-sales-customers', 'status' => $contact_key ], admin_url( 'admin.php' ) ); ?>">
                                                    <?php
                                                        if ( $contact_key == 'customer' ) {
                                                            echo sprintf( _n( '%s Customer', '%s Customers', $contact_value['count'], 'erp' ), number_format_i18n( $contact_value['count'] ), 0 );
                                                        } else if ( $contact_key == 'opportunity' ) {
                                                            echo sprintf( _n( '%s Opportunity', '%s Opportunites', $contact_value['count'], 'erp' ), number_format_i18n( $contact_value['count'] ), 0 );
                                                        } else {
                                                            echo sprintf( _n( '%s Lead', '%s Leads', $contact_value['count'], 'erp' ), number_format_i18n( $contact_value['count'] ), 0 );
                                                        }
                                                    ?>
                                                </a>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="badge-footer wp-ui-highlight">
                                <a href="<?php echo admin_url( 'admin.php?page=erp-sales-customers' ); ?>"><?php _e( 'View all Contacts', 'erp' ); ?></a>
                            </div>
                        </div><!-- .badge-wrap -->

                        <div class="col-3 badge-wrap">
                            <div class="row">
                                <div class="badge-inner total-counter col-2">
                                    <h3><?php echo number_format_i18n( $companies_count['all']['count'], 0 ); ?></h3>
                                    <p>
                                        <?php echo sprintf( _n( 'Company', 'Companies', $companies_count['all']['count'], 'erp' ), number_format_i18n( $companies_count['all']['count'] ), 0 ); ?>
                                    </p>
                                </div>

                                <div class="badge-inner col-4">
                                    <ul class="erp-dashboard-total-counter-list">
                                        <?php
                                        foreach ( $companies_count as $company_key => $company_value ) {
                                            if ( $company_key == 'all' ) {
                                                continue;
                                            }
                                            ?>
                                            <li>
                                                <a href="<?php echo add_query_arg( [ 'page' => 'erp-sales-customers', 'status' => $company_key ], admin_url( 'admin.php' ) ); ?>">
                                                    <?php
                                                        if ( $company_key == 'customer' ) {
                                                            echo sprintf( _n( '%s Customer', '%s Customers', $company_value['count'], 'erp' ), number_format_i18n( $company_value['count'] ), 0 );
                                                        } else if ( $company_key == 'opportunity' ) {
                                                            echo sprintf( _n( '%s Opportunity', '%s Opportunites', $company_value['count'], 'erp' ), number_format_i18n( $company_value['count'] ), 0 );
                                                        } else {
                                                            echo sprintf( _n( '%s Lead', '%s Leads', $company_value['count'], 'erp' ), number_format_i18n( $company_value['count'] ), 0 );
                                                        }
                                                    ?>
                                                </a>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>

                            </div>
                            <div class="badge-footer wp-ui-highlight">
                                <a href="<?php echo admin_url( 'admin.php?page=erp-sales-companies' ); ?>"><?php _e( 'View all Companies', 'erp' ); ?></a>
                            </div>
                        </div><!-- .badge-wrap -->
                    </div>
                </div><!-- .badge-container -->

            <?php do_action( 'erp_crm_dashboard_widgets_left' ); ?>

                <div id="placeholder" style="width: 500px; height: 280px;"></div>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">
            <?php do_action( 'erp_crm_dashboard_widgets_right' ); ?>
        </div>

    </div>
</div>

<script>
    ;(function($) {

        $(document).ready( function() {
            var bar_customised_1 = [[1388534400000, 120], [1391212800000, 70],  [1393632000000, 100], [1396310400000, 60], [1398902400000, 35]];
            var bar_customised_2 = [[1388534400000, 90], [1391212800000, 60], [1393632000000, 30], [1396310400000, 73], [1398902400000, 30]];
            var bar_customised_3 = [[1388534400000, 80], [1391212800000, 40], [1393632000000, 47], [1396310400000, 22], [1398902400000, 24]];

            var data = [
                { label: "Series 1", data: bar_customised_1 },
                { label: "Series 2", data: bar_customised_2 },
                { label: "Series 3", data: bar_customised_3 }
            ];

            $.plot($("#placeholder"), data, {
                series: {
                    bars: {
                        show: true,
                        barWidth: 12*24*60*60*350,
                        lineWidth: 0,
                        order: 1,
                        fillColor: {
                            colors: [{
                                opacity: 1
                            }, {
                                opacity: 0.7
                            }]
                        }
                    }
                },
                xaxis: {
                    mode: "time",
                    min: 1387497600000,
                    max: 1400112000000,
                    tickLength: 0,
                    tickSize: [1, "month"],
                    axisLabel: 'Month',
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 13,
                    axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                    axisLabelPadding: 15
                },
                yaxis: {
                    axisLabel: 'Value',
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 13,
                    axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                    axisLabelPadding: 5
                },
                grid: {
                    hoverable: true,
                    borderWidth: 0
                },
                legend: {
                    backgroundColor: "#EEE",
                    labelBoxBorderColor: "none"
                },
                colors: ["#AA4643", "#89A54E", "#4572A7"]
            });

            function show_tooltip(x, y, contents, z) {
                $('<div id="bar_tooltip">' + contents + '</div>').css({
                    top: y - 45,
                    left: x - 28,
                    'border-color': z,
                }).appendTo("body").fadeIn();
            }

            function get_month_name(month_timestamp) {
                var month_date = new Date(month_timestamp);
                var month_numeric = month_date.getMonth();
                var month_array = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                var month_string = month_array[month_numeric];

                return month_string;
            }

            var previous_point = null;
            var previous_label = null;

            $("#placeholder").on("plothover", function (event, pos, item) {
                if (item) {
                    if ((previous_point != item.dataIndex) || (previous_label != item.series.label)) {
                        previous_point = item.dataIndex;
                        previous_label = item.series.label;

                        $("#bar_tooltip").remove();

                        var x = get_month_name(item.series.data[item.dataIndex][0]),
                            y = item.datapoint[1],
                            z = item.series.color;

                        show_tooltip(item.pageX, item.pageY,
                            "<div style='text-align: center;'><b>" + item.series.label + "</b><br />" + x + ": " + y + "</div>",
                            z);
                    }
                } else {
                    $("#bar_tooltip").remove();
                    previous_point = null;
                    previous_label = null;
                }
            });
        });

    })(jQuery)

</script>


