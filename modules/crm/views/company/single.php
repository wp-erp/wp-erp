<div class="wrap erp erp-crm-customer erp-single-customer" id="wp-erp">

    <h2><?php _e( 'Company #', 'wp-erp' ); echo $customer->id; ?>
        <a href="<?php echo add_query_arg( ['page' => 'erp-sales-companies'], admin_url( 'admin.php' ) ); ?>" id="erp-contact-list" class="add-new-h2"><?php _e( 'Back to Company list', 'wp-erp' ); ?></a>
        <span class="edit">
            <a href="#" data-id="<?php echo $customer->id; ?>" data-single_view="1" title="<?php _e( 'Edit this Company', 'wp-erp' ); ?>" class="add-new-h2"><?php _e( 'Edit this Company', 'wp-erp' ); ?></a>
        </span>
    </h2>

    <div class="erp-grid-container erp-single-customer-content">
        <div class="row">

            <div class="col-2 column-left erp-single-customer-row">

                <div class="left-content">

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

                                <?php if ( $customer->mobile ): ?>
                                    <p>
                                        <i class="fa fa-phone"></i>&nbsp;
                                        <?php echo $customer->mobile; ?>
                                    </p>
                                <?php endif ?>

                                <ul class="erp-list list-inline social-profile">
                                    <?php $social_field = erp_crm_get_social_field(); ?>

                                    <?php foreach ( $social_field as $social_key => $social_value ) : ?>
                                        <?php $social_field_data = $customer->get_meta( $social_key, true ); ?>

                                        <?php if ( ! empty( $social_field_data ) ): ?>
                                            <li><a href="<?php echo $social_field_data; ?>"><?php echo $social_value['icon']; ?></a></li>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </ul>

                            </div>
                        </div>
                    </div>

                    <div class="postbox customer-basic-info">
                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php _e( 'Basic Info', 'wp-erp' ); ?></span></h3>
                        <div class="inside">
                            <ul class="erp-list separated">
                                <li><?php erp_print_key_value( __( 'Name', 'wp-erp' ), $customer->company ); ?></li>
                                <li><?php erp_print_key_value( __( 'Phone', 'wp-erp' ), $customer->phone ); ?></li>
                                <li><?php erp_print_key_value( __( 'Fax', 'wp-erp' ), $customer->fax ); ?></li>
                                <li><?php erp_print_key_value( __( 'Website', 'wp-erp' ), erp_get_clickable( 'url', $customer->website ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Street 1', 'wp-erp' ), $customer->street_1 ); ?></li>
                                <li><?php erp_print_key_value( __( 'Street 2', 'wp-erp' ), $customer->street_2 ); ?></li>
                                <li><?php erp_print_key_value( __( 'City', 'wp-erp' ), $customer->city ); ?></li>
                                <li><?php erp_print_key_value( __( 'State', 'wp-erp' ), erp_get_state_name( $customer->country, $customer->state ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Country', 'wp-erp' ), erp_get_country_name( $customer->country ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Postal Code', 'wp-erp' ), $customer->postal_code ); ?></li>

                                <?php do_action( 'erp-hr-employee-single-basic', $customer ); ?>
                            </ul>
                        </div>
                    </div><!-- .postbox -->

                    <div class="postbox customer-company-info">
                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php echo sprintf( '%s\'s %s', $customer->company, __( 'Contacts', 'wp-erp' ) ); ?></span></h3>
                        <div class="inside company-profile-content">
                            <div class="company-list">
                                <?php $assing_customers = erp_crm_company_get_customers( $customer->id ); ?>

                                <?php foreach ( $assing_customers as $assing_customer ) : ?>
                                    <div class="postbox closed">
                                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                                        <h3 class="erp-hndle">
                                            <span class="customer-avatar"><?php echo erp_crm_get_avatar( $assing_customer->id, 20 ) ?></span>
                                            <span class="customer-name">
                                                <a href="<?php echo erp_crm_get_customer_details_url( $assing_customer->id ) ?>" target="_blank">
                                                    <?php echo $assing_customer->first_name . ' ' . $assing_customer->last_name; ?>
                                                </a>
                                            </span>
                                        </h3>
                                        <div class="action">
                                            <a href="#" class="erp-customer-delete-company" data-id="<?php echo $assing_customer->com_cus_id; ?>" data-action="erp-crm-customer-remove-company"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                        <div class="inside company-profile-content">
                                            <ul class="erp-list separated">
                                                <li><?php erp_print_key_value( __( 'Phone', 'wp-erp' ), $assing_customer->phone ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Fax', 'wp-erp' ), $assing_customer->fax ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Website', 'wp-erp' ), erp_get_clickable( 'url', $assing_customer->website ) ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Street 1', 'wp-erp' ), $assing_customer->street_1 ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Street 2', 'wp-erp' ), $assing_customer->street_2 ); ?></li>
                                                <li><?php erp_print_key_value( __( 'City', 'wp-erp' ), $assing_customer->city ); ?></li>
                                                <li><?php erp_print_key_value( __( 'State', 'wp-erp' ), erp_get_state_name( $assing_customer->country, $assing_customer->state ) ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Country', 'wp-erp' ), erp_get_country_name( $assing_customer->country ) ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Postal Code', 'wp-erp' ), $assing_customer->postal_code ); ?></li>
                                            </ul>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                                <a href="#" data-id="<?php echo $customer->id; ?>" data-type="assign_customer" title="<?php _e( 'Assign a Contact', 'wp-erp' ); ?>" class="button button-primary" id="erp-customer-add-company"><i class="fa fa-plus"></i>&nbsp;<?php _e( 'Assign a Contact', 'wp-erp' ); ?></a>
                            </div>
                        </div>
                    </div><!-- .postbox -->

                    <div class="postbox customer-mail-subscriber-info">
                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php _e( 'Mail Subscriber Group', 'wp-erp' ); ?></span></h3>
                        <div class="inside contact-group-content">
                            <div class="contact-group-list">
                                <?php $subscribe_groups = erp_crm_get_user_assignable_groups( $customer->id ); ?>
                                <?php if ( $subscribe_groups ): ?>
                                    <?php foreach ( $subscribe_groups as $key => $groups ): ?>
                                        <p>
                                            <?php
                                                echo $groups['groups']['name'];
                                                $info_messg = ( $groups['status'] == 'subscribe' )
                                                                ? sprintf( '%s %s', __( 'Subscribed on'), erp_format_date( $groups['subscribe_at'] ) )
                                                                : sprintf( '%s %s', __( 'Unsubscribed on'), erp_format_date( $groups['unsubscribe_at'] ) );
                                            ?>
                                            <span class="erp-crm-tips" title="<?php echo $info_messg; ?>">
                                                <i class="fa fa-info-circle"></i>
                                            </span>
                                        </p>
                                    <?php endforeach; ?>
                                <?php endif ?>

                                <a href="#" id="erp-contact-update-assign-group" data-id="<?php echo $customer->id; ?>" title="<?php _e( 'Assign Contact Groups', 'wp-erp' ); ?>"><i class="fa fa-plus"></i> <?php _e( 'Add any mail groups', 'wp-erp' ); ?></a>
                            </div>
                        </div>
                    </div><!-- .postbox -->
                </div>
            </div>

            <div class="col-4 column-right">
                <?php include WPERP_CRM_VIEWS . '/company/feeds.php'; ?>
            </div>

        </div>
    </div>

</div>