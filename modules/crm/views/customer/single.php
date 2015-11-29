<div class="wrap erp erp-crm-customer erp-single-customer" id="wp-erp">

    <h2><?php _e( 'Customer #', 'wp-erp' ); echo $customer->id; ?>
        <a href="<?php echo add_query_arg( ['page' => 'erp-sales-customers'], admin_url( 'admin.php' ) ); ?>" id="erp-contact-list" class="add-new-h2"><?php _e( 'Back to Customer list', 'wp-erp' ); ?></a>
        <span class="edit">
            <a href="#" data-id="<?php echo $customer->id; ?>" title="<?php _e( 'Edit this customer', 'wp-erp' ); ?>" class="add-new-h2"><?php _e( 'Edit this customer', 'wp-erp' ); ?></a>
        </span>
    </h2>

    <div class="erp-grid-container erp-single-customer-content">
        <div class="row">

            <div class="col-2 column-left">

                <div class="customer-image-wraper">
                    <div class="row">
                        <div class="col-2 avatar">
                            <?php echo $customer->get_avatar(100) ?>
                        </div>
                        <div class="col-4 details">
                            <h3><?php echo $customer->get_full_name(); ?></h3>
                            <p>
                                <i class="fa fa-envelope"></i>&nbsp;
                                <?php echo erp_get_clickable( 'email', $customer->email ); ?>
                            </p>
                            <p>
                                <i class="fa fa-phone"></i>&nbsp;
                                <?php echo $customer->mobile; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="postbox customr-basic-info">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                    <h3 class="hndle"><span><?php _e( 'Basic Info', 'wp-erp' ); ?></span></h3>
                    <div class="inside">
                        <ul class="erp-list separated">
                            <li><?php erp_print_key_value( __( 'First Name', 'wp-erp' ), $customer->first_name ); ?></li>
                            <li><?php erp_print_key_value( __( 'Last Name', 'wp-erp' ), $customer->last_name ); ?></li>
                            <li><?php erp_print_key_value( __( 'Phone', 'wp-erp' ), $customer->phone ); ?></li>
                            <li><?php erp_print_key_value( __( 'Fax', 'wp-erp' ), $customer->fax ); ?></li>
                            <li><?php erp_print_key_value( __( 'Website', 'wp-erp' ), erp_get_clickable( 'url', $customer->website ) ); ?></li>
                            <li><?php erp_print_key_value( __( 'Street 1', 'wp-erp' ), $customer->street_1 ); ?></li>
                            <li><?php erp_print_key_value( __( 'Street 2', 'wp-erp' ), $customer->street_2 ); ?></li>
                            <li><?php erp_print_key_value( __( 'City', 'wp-erp' ), $customer->city ); ?></li>
                            <li><?php erp_print_key_value( __( 'State', 'wp-erp' ), erp_get_state_name( $customer->country, $customer->state ) ); ?></li>
                            <li><?php erp_print_key_value( __( 'Country', 'wp-erp' ), erp_get_country_name( $customer->country ) ); ?></li>

                            <?php do_action( 'erp-hr-employee-single-basic', $customer ); ?>
                        </ul>
                    </div>
                </div><!-- .postbox -->

                <div class="postbox customr-basic-info">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                    <h3 class="hndle"><span><?php echo sprintf( '%s\'s %s', $customer->first_name, __( 'Company', 'wp-erp' ) ); ?></span></h3>
                    <div class="inside">

                    </div>
                </div><!-- .postbox -->

                <div class="postbox customr-basic-info">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                    <h3 class="hndle"><span><?php _e( 'Social Info', 'wp-erp' ); ?></span></h3>
                    <div class="inside">

                    </div>
                </div><!-- .postbox -->

            </div>

            <div class="col-4 column-right">

            </div>

        </div>
    </div>

</div>