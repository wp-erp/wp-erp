<?php

namespace WeDevs\ERP\CRM;

class Admin_Dashboard {

    public function __construct() {
        // Only hook in admin parts if the user has admin access
        add_action( 'wp_dashboard_setup', array( $this, 'init' ), 10 );
    }

    /**
     * Init dashboard widgets.
     *
     * @since 1.2.6
     */
    public function init() {
        wp_add_dashboard_widget( 'erp_dashboard_customer_statics', __( 'Customer Statistics', 'erp' ), array(
            $this,
            'customer_statics'
        ) );
    }

    function customer_statics() {
        wp_enqueue_script( 'erp-jvectormap' );
        wp_enqueue_script( 'erp-jvectormap-world-mill' );
        wp_enqueue_style( 'erp-jvectormap' );

        echo '<div id="erp-hr-customer-statics" style="width: 100%; height: 300px;"></div>';
        $customer_countries = array();
        if ( false == get_transient( 'erp_customer_countries_widget' ) ) {
            global $wpdb;
            $countries = $wpdb->get_results( 'SELECT country FROM ' . $wpdb->prefix . 'erp_peoples', OBJECT );

            $codes     = array();
            foreach ( $countries as $code_of ) {
                if( !is_null($code_of->country)){
                    $codes[] = $code_of->country;
                }
            }

            $customer_countries = array_count_values( $codes );
            set_transient( 'erp_customer_countries_widget', $customer_countries, time() + ( 3 * HOUR_IN_SECONDS ) );
        } else {
            $customer_countries = get_transient( 'erp_customer_countries_widget' );
        }

        ob_start();
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery('#erp-hr-customer-statics').vectorMap({
                    map: 'world_mill',
                    backgroundColor: '#e0e0e0',
                    zoomOnScroll: false,
                    series: {
                        regions: [{
                            values: <?php echo json_encode( $customer_countries ); ?>,
                            scale: ['#C8EEFF', '#0071A4'],
                            normalizeFunction: 'polynomial'
                        }]
                    },
                    onRegionTipShow: function (e, el, code) {
                        if (typeof <?php echo json_encode( $customer_countries ); ?>[code] === 'undefined') {
                            el.html('No data');
                        } else {
                            el.html(el.html() + ': ' + <?php echo json_encode( $customer_countries ); ?>[code]);
                        }
                    }
                });
            });
        </script>
        <?php
        $output = ob_get_contents();
        ob_get_clean();
        echo $output;

    }

}


