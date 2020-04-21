<div class="wrap erp-overview">

    <h2 class="erp-page-title"><?php esc_html_e( 'Overview', 'erp' ); ?></h2>

    <div class="erp-grid-container">

        <div class="col-3">
            <?php
                if ( erp_is_module_active( 'hrm' ) ) {
                    include WPERP_HRM_VIEWS . '/dashboard-badge.php';
                }
            ?>

            <div class="erp-badge-box">
                <h2><?php esc_html_e( 'Latest ERP Blogs', 'erp' ); ?></h2>

                <?php $xml = erp_web_feed(); ?>
                <ul class="erp-rss-feed">
                    <?php if ( property_exists( $xml, 'channel' ) ) { ?>
                        <?php foreach( $xml->channel->item as $entry ) : ?>
                        <li><a target="_blank" href="<?php echo esc_url( $entry->link.'?utm_source=ERP+Dashboard&utm_medium=CTA&utm_content=Backend&utm_campaign=Docs' ); ?>" title="<?php echo esc_html(  $entry->title ); ?>"><?php echo esc_html( $entry->title ); ?></a></li>
                        <?php endforeach; ?>
                    <?php } else {
                        echo "<li>" . esc_html_e( 'No available blog right now.', 'erp' )  . "</li>";
                    } ?>
                </ul>

                <div class="erp-newsletter">
                    <h3><?php esc_html_e( 'Stay up-to-date', 'erp' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'Don\'t miss any updates of our new templates and extensions and all the astonishing offers we bring for you.', 'erp' ); ?>
                    </p>
                    <div class="erp-form-wrap">
                        <input type="email" class="email-subscribe" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>">
                        <button class="button email-subscribe-btn"><?php esc_html_e( 'Subscribe', 'erp' ); ?></button>
                    </div>
                    <div class="erp-thank-you"></div>
                </div>
            </div><!-- .badge-box -->

        </div>
        <div class="col-3">
            <?php
                if ( erp_is_module_active( 'crm' ) ) {
                    include WPERP_CRM_VIEWS . '/dashboard-badge.php';
                }

                if ( erp_is_module_active('accounting') ) {
                    include ERP_ACCOUNTING_VIEWS . '/dashboard/dashboard.php';
                }
            ?>
        </div>

    </div>

</div>
