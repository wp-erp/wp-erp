<?php

namespace WeDevs\ERP\CRM\Admin;

class AdminDashboard {
    public function __construct() {
        // Only hook in admin parts if the user has admin access
        add_action( 'wp_dashboard_setup', [ $this, 'init' ], 10 );
    }

    /**
     * Init dashboard widgets.
     *
     * @since 1.2.6
     */
    public function init() {
        /* wp_add_dashboard_widget( 'erp_dashboard_customer_statics', __( 'Customer Statistics', 'erp' ), array(
             $this,
             'customer_statics'
         ) );*/ // Removed Customer Statistic from Admin dashboard
    }
}
