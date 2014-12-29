<div class="wrap erp">
    <h2><?php _e( 'Company', 'wp-erp' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-company&action=new' ); ?>" class="add-new-h2"><?php _e( 'New Company', 'wp-erp' ); ?></a></h2>

    <div class="metabox-holder company-accounts">
        <?php
        $companies = erp_get_companies();

        if ( $companies ) {
            foreach ($companies as $index => $comp) {
                $company = new \WeDevs\ERP\Company( $comp );
                ?>
                <div class="postbox account">
                    <div class="inside clearfix">
                        <div class="logo-area">
                            <?php echo $company->get_logo(); ?>
                        </div><!-- .logo-area -->

                        <div class="content-area">
                            <h2><?php echo $company->name; ?> <a href="<?php echo $company->get_edit_url(); ?>">Edit</a></h2>

                            <address class="address">
                                <?php echo $company->get_formatted_address(); ?>
                            </address>
                        </div><!-- .content-area -->
                    </div><!-- .inside -->
                </div><!-- .account -->
                <?php
            }
        }
        ?>
    </div><!-- .metabox-holder -->

</div>