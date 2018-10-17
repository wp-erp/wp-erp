<div class="wrap erp hrm-dashboard erp-overview">

    <h2><?php _e( 'WP ERP Overview', 'erp' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

            <?php
            
            if ( erp_is_module_active( 'hrm' ) ) {
                include WPERP_HRM_VIEWS . '/dashboard-badge.php';
            }

            if ( erp_is_module_active( 'crm' ) ) {
                include WPERP_CRM_VIEWS . '/dashboard-badge.php';
            }

            if ( erp_is_module_active('accounting') ) {
                erp_admin_dash_metabox( __( 'Cash & Bank Balance', 'erp' ), 'erp_ac_dashboard_banks', 'bank-balance' );
                erp_admin_dash_metabox( __( 'Revenues', 'erp' ), 'erp_ac_dashboard_net_income', 'bank-balance' );
            }

            ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">

            <?php do_action( 'erp_overview_widgets_right' ); ?>

        </div>

    </div>

</div>
