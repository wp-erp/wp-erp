<?php
    $contacts_count  = erp_crm_customer_get_status_count( 'contact' );
    $companies_count = erp_crm_customer_get_status_count( 'company' );
?>
<div class="wrap erp crm-dashboard">
    <h2><?php _e( 'CRM Dashboard', 'erp' ); ?></h2>

    <div class="erp-single-container">
        <div class="erp-area-left">
            <div class="erp-info-box">
                <div class="erp-info-box-item">
                    <div class="erp-info-box-item-inner">
                        <div class="erp-info-box-content">
                            <div class="erp-info-box-content-row">
                                <div class="erp-info-box-content-left">
                                    <h3><?php echo number_format_i18n( $contacts_count['all']['count'], 0 ); ?></h3>
                                    <p>
                                        <?php echo sprintf( _n( 'Contact', 'Contacts', $contacts_count['all']['count'], 'erp' ), number_format_i18n( $companies_count['all']['count'] ), 0 ); ?>
                                    </p>
                                </div>
                                <div class="erp-info-box-content-right">
                                    <ul class="erp-info-box-list">
                                        <?php
                                        foreach ( $contacts_count as $contact_key => $contact_value ) {
                                            if ( $contact_key == 'all' || $contact_key == 'trash' ) {
                                                continue;
                                            }
                                            ?>
                                            <li>
                                                <a href="<?php echo add_query_arg( [ 'page' => 'erp-crm','section' => 'contacts', 'status' => $contact_key ], admin_url( 'admin.php' ) ); ?>">
                                                    <i class="fa fa-square" aria-hidden="true"></i>&nbsp;
                                                    <?php echo $contact_value['count'] . ' ' . $contact_value['label']; ?>
                                                </a>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="erp-info-box-footer">
                            <a href="<?php echo admin_url( 'admin.php?page=erp-crm&section=contacts' ); ?>"><?php _e( 'View all Contacts', 'erp' ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="erp-info-box-item">
                    <div class="erp-info-box-item-inner">
                        <div class="erp-info-box-content">
                            <div class="erp-info-box-content-row">
                                <div class="erp-info-box-content-left">
                                    <h3><?php echo number_format_i18n( $companies_count['all']['count'], 0 ); ?></h3>
                                    <p>
                                        <?php echo sprintf( _n( 'Company', 'Companies', $companies_count['all']['count'], 'erp' ), number_format_i18n( $companies_count['all']['count'] ), 0 ); ?>
                                    </p>
                                </div>
                                <div class="erp-info-box-content-right">
                                    <ul class="erp-info-box-list">
                                        <?php
                                        foreach ( $companies_count as $company_key => $company_value ) {
                                            if ( $company_key == 'all' || $company_key == 'trash' ) {
                                                continue;
                                            }
                                            ?>
                                            <li>
                                                <a href="<?php echo add_query_arg( [ 'page' => 'erp-crm', 'section' => 'companies', 'status' => $company_key ], admin_url( 'admin.php' ) ); ?>">
                                                    <i class="fa fa-square" aria-hidden="true"></i>&nbsp;
                                                    <?php echo $company_value['count'] . ' ' . $company_value['label']; ?>
                                                </a>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="erp-info-box-footer">
                            <a href="<?php echo add_query_arg( ['page' => 'erp-crm', 'section' => 'companies'], admin_url('admin.php') ); ?>"><?php _e( 'View all Companies', 'erp' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <?php do_action( 'erp_crm_dashboard_widgets_left' ); ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">
            <?php do_action( 'erp_crm_dashboard_widgets_right' ); ?>
        </div>
    </div>
</div>
