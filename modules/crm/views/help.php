<div class="wrap">
    <h1>
        <?php esc_attr_e( 'CRM Help', 'erp' ); ?>
        <a href="https://wperp.com/docs/crm/" target="_blank" class="page-title-action">
            <?php esc_attr_e( 'View all Documentations', 'erp' ); ?>
        </a>
    </h1>
    <?php
    $erp_doc_sections = array(
        __( 'General', 'erp' )            => array(
            __( 'What are the differences between CRM life stages?', 'erp' ) => 'https://wperp.com/docs/crm/getting-started/contact-stages/',
        ),
        __( 'Contact Management', 'erp' ) => array(
            __( 'How to add a new contact?', 'erp' )                                      => 'https://wperp.com/docs/crm/contacts-management/adding-a-new-contact/',
            __( 'How can I search contacts by using filters and search segment?', 'erp' ) => 'https://wperp.com/docs/crm/contacts-management/contacts-filtering/',
            __( 'How to send Emails to contact by using templates?', 'erp' )              => 'https://wperp.com/docs/crm/contacts-management/sending-a-mail-from-template/',
            __( 'How can I set up a meeting with a contact?', 'erp' )                     => 'https://wperp.com/docs/crm/contacts-management/setting-up-a-meeting/',
            __( 'How to assign contacts to the companies?', 'erp' )                       => 'https://wperp.com/docs/crm/contacts-management/assigning-a-contact-to-a-company/',
        ),
        __( 'Company Management', 'erp' ) => array(
            __( 'How to add a new company?', 'erp' )                => 'https://wperp.com/docs/crm/company-management/creating-or-updating-a-new-company/',
            __( 'How to add a company to a contact group?', 'erp' ) => 'https://wperp.com/docs/crm/company-management/adding-a-company-to-a-contact-group/',
        ),
        __( 'Miscellaneous', 'erp' )      => array(
            __( 'How to create contacts group?', 'erp' )                 => 'https://wperp.com/docs/crm/contact-groups/creating-groups/',
            __( 'How to create an event or log from calendar?', 'erp' )  => 'https://wperp.com/docs/crm/contact-groups/creating-groups/',
            __( 'How to use Subscription Form (CRM) in WP ERP?', 'erp' ) => 'https://wperp.com/docs/crm/subscription-forms/',
            __( 'Do you have tutorials on youtube?', 'erp' )             => 'https://wperp.com/docs/crm/tutorial-videos-on-youtube/'
        )
    );

    $sections = apply_filters( 'erp_crm_help_docs', $erp_doc_sections );

    if ( ! empty( $sections ) ):?>
        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <?php foreach ( $sections as $section_title => $docs ): ?>
                    <div class="erp-help-section postbox-container">
                        <div class="metabox-holder">

                            <div class="meta-box-sortables">

                                <div class="postbox">
                                    <h2 class="hndle"><?php echo esc_html( $section_title ); ?></h2>

                                    <?php if ( !empty($docs) ) { ?>
                                        <div class="erp-help-questions">
                                            <ul>
                                                <?php foreach ($docs as $title => $link) { ?>
                                                    <?php
                                                    $tracking_url = add_query_arg(
                                                        array(
                                                            'utm_source'   => 'doc',
                                                            'utm_medium'   => 'erp',
                                                            'utm_campaign' => 'manik',
                                                            'utm_content'  => 'aion',
                                                        ),
                                                        untrailingslashit($link)
                                                    );
                                                    ?>

                                                    <li>
                                                        <a href="<?php echo esc_url_raw( $tracking_url ); ?>" target="_blank"><?php echo esc_html( $title ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>

    <?php endif; ?>

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
