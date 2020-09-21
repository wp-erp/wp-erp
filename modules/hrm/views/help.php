<div class="wrap">
    <h1>
        <?php esc_html_e( 'HRM Help', 'erp' ); ?>
        <a href="https://wperp.com/docs/hr/" target="_blank" class="page-title-action">
            <?php esc_html_e( 'View all Documentations', 'erp' ); ?>
        </a>
    </h1>
    <?php
    $erp_doc_sections = [
        __( 'General', 'erp' )             => [
            __( 'How to setup my company details in WP ERP?', 'erp' )               => 'https://wperp.com/docs/erp-core/settings/company/',
            __( 'How to disable any module (like-HRM, CRM or Accounting)?', 'erp' ) => 'https://wperp.com/docs/erp-core/settings/modules-management/',
            __( 'How to setup basic erp settings?', 'erp' )                         => 'https://wperp.com/docs/erp-core/settings/global-settings/',
            __( 'How to translate WP ERP?', 'erp' )                                 => 'https://wperp.com/docs/erp-core/how-to-translate-wp-erp-plugin/',
            __( 'How to install developed version of WP ERP (github)?', 'erp' )     => 'https://wperp.com/docs/erp-core/installation/',
        ],
        __( 'Employee Management', 'erp' ) => [
            __( 'How to add an employee?', 'erp' )                              => 'https://wperp.com/docs/hr/managing-employee/adding-employees/',
            __( 'How to add and manage departments?', 'erp' )                   => 'https://wperp.com/docs/hr/managing-employee/create-manage-department-hrm/',
            __( 'How to add and manage designation?', 'erp' )                   => 'https://wperp.com/docs/hr/managing-employee/creating-designations/',
            __( 'How to assign Department & Designation to employees?', 'erp' ) => 'https://wperp.com/docs/hr/managing-employee/add-department-designation-employee/',
            __( 'How to manage permissions for the employees?', 'erp' )         => 'https://wperp.com/docs/hr/managing-employee/permission-management/',
        ],
        __( 'Leave Management', 'erp' )    => [
            __( 'How to create Leave Policy?', 'erp' )                     => 'https://wperp.com/docs/hr/leave-management/create-leave-policy/',
            __( 'How to create Leave Entitlement?', 'erp' )                => 'https://wperp.com/docs/hr/leave-management/leave-entitlements/',
            __( 'How to create Leave Request?', 'erp' )                    => 'https://wperp.com/docs/hr/leave-management/creating-leave-requests/',
            __( 'How to Manage (Accept / Reject) Leave Requests?', 'erp' ) => 'https://wperp.com/docs/hr/leave-management/managing-requests/',
        ],
        __( 'Miscellaneous', 'erp' )       => [
            __( 'How to create announcement?', 'erp' )                  => 'https://wperp.com/docs/hr/announcement/',
            __( 'How to setup working days for the employees?', 'erp' ) => 'https://wperp.com/docs/hr/settings/work-days/',
            __( 'How to generate reports?', 'erp' )                     => 'https://wperp.com/docs/hr/reporting/',
            __( 'Do you have any video tutorial on HRM?', 'erp' )       => 'https://wperp.com/tv/category/hr/',
        ],
    ];

    $sections = apply_filters( 'erp_hr_help_docs', $erp_doc_sections );

    if ( ! empty( $sections ) ) { ?>
        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <?php foreach ( $sections as $section_title => $docs ) { ?>
                    <div class="erp-help-section postbox-container">
                        <div class="metabox-holder">

                            <div class="meta-box-sortables">

                                <div class="postbox">
                                    <h2 class="hndle"><?php echo esc_html( $section_title ); ?></h2>

                                    <?php if ( !empty( $docs ) ) { ?>
                                        <div class="erp-help-questions">
                                            <ul>
                                                <?php foreach ( $docs as $title => $link ) { ?>
                                                    <?php
                                                    $tracking_url = add_query_arg(
                                                        [
                                                            'utm_source'   => 'doc',
                                                            'utm_medium'   => 'erp',
                                                            'utm_campaign' => 'manik',
                                                            'utm_content'  => 'aion',
                                                        ],
                                                        untrailingslashit( $link )
                                                    );
                                                    ?>

                                                    <li><a href="<?php echo esc_url_raw( $tracking_url ); ?>" target="_blank"><?php echo esc_html( $title ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>

                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } else { ?>

    <?php } ?>

</div>



<style type="text/css" media="screen">
    .erp-help-questions li {
        margin: 0;
        border-bottom: 1px solid #eee;
    }

    .erp-help-questions li a {
        padding: 10px 15px;
        display: block;
    }

    .erp-help-questions li a:hover {
        background-color: #F5F5F5;
    }

    .erp-help-questions li:last-child {
        border-bottom: none;
    }

    .erp-help-questions li .dashicons {
        float: right;
        color: #ccc;
        margin-top: -3px;
    }

    @media screen and (min-width: 960px) {
      .erp-help-section .postbox-container{
          width: 100% !important;
      }

        .erp-help-section:nth-child(odd){
            clear:both !important;
        }

    }
</style>
