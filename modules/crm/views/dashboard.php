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
                                            if ( $contact_key == 'all' || $contact_key == 'trash' ) {
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
                                                        } elseif( $contact_key == 'subscriber' ) {
                                                            echo sprintf( _n( '%s Subscriber', '%s Subscribers', $contact_value['count'], 'erp' ), number_format_i18n( $contact_value['count'] ), 0 );
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
                                            if ( $company_key == 'all' || $company_key == 'trash' ) {
                                                continue;
                                            }
                                            ?>
                                            <li>
                                                <a href="<?php echo add_query_arg( [ 'page' => 'erp-sales-companies', 'status' => $company_key ], admin_url( 'admin.php' ) ); ?>">
                                                    <?php
                                                        if ( $company_key == 'customer' ) {
                                                            echo sprintf( _n( '%s Customer', '%s Customers', $company_value['count'], 'erp' ), number_format_i18n( $company_value['count'] ), 0 );
                                                        } else if ( $company_key == 'opportunity' ) {
                                                            echo sprintf( _n( '%s Opportunity', '%s Opportunites', $company_value['count'], 'erp' ), number_format_i18n( $company_value['count'] ), 0 );
                                                        } elseif ( $company_key == 'subscriber' ) {
                                                            echo sprintf( _n( '%s Subscriber', '%s Subscribers', $company_value['count'], 'erp' ), number_format_i18n( $company_value['count'] ), 0 );
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

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">
            <?php do_action( 'erp_crm_dashboard_widgets_right' ); ?>
        </div>

    </div>
</div>
