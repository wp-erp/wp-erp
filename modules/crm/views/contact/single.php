<div class="wrap erp erp-crm-customer erp-single-customer" id="wp-erp">

    <h2><?php _e( 'Contact #', 'wp-erp' ); echo $customer->id; ?>
        <a href="<?php echo add_query_arg( ['page' => 'erp-sales-customers'], admin_url( 'admin.php' ) ); ?>" id="erp-contact-list" class="add-new-h2"><?php _e( 'Back to Contact list', 'wp-erp' ); ?></a>

        <?php if ( current_user_can( 'erp_crm_edit_contact', $customer->id ) ): ?>
            <span class="edit">
                <a href="#" data-id="<?php echo $customer->id; ?>" data-single_view="1" title="<?php _e( 'Edit this Contact', 'wp-erp' ); ?>" class="add-new-h2"><?php _e( 'Edit this Contact', 'wp-erp' ); ?></a>
            </span>
        <?php endif ?>
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
                                <li><?php erp_print_key_value( __( 'First Name', 'wp-erp' ), $customer->first_name ); ?></li>
                                <li><?php erp_print_key_value( __( 'Last Name', 'wp-erp' ), $customer->last_name ); ?></li>
                                <li><?php erp_print_key_value( __( 'Date of Birth', 'wp-erp' ), $customer->get_birthday() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Phone', 'wp-erp' ), $customer->phone ); ?></li>
                                <li><?php erp_print_key_value( __( 'Fax', 'wp-erp' ), $customer->fax ); ?></li>
                                <li><?php erp_print_key_value( __( 'Website', 'wp-erp' ), erp_get_clickable( 'url', $customer->website ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Street 1', 'wp-erp' ), $customer->street_1 ); ?></li>
                                <li><?php erp_print_key_value( __( 'Street 2', 'wp-erp' ), $customer->street_2 ); ?></li>
                                <li><?php erp_print_key_value( __( 'City', 'wp-erp' ), $customer->city ); ?></li>
                                <li><?php erp_print_key_value( __( 'State', 'wp-erp' ), erp_get_state_name( $customer->country, $customer->state ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Country', 'wp-erp' ), erp_get_country_name( $customer->country ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Postal Code', 'wp-erp' ), $customer->postal_code ); ?></li>
                                <li><?php erp_print_key_value( __( 'Source', 'wp-erp' ), $customer->get_source() ); ?></li>

                                <?php do_action( 'erp-hr-employee-single-basic', $customer ); ?>
                            </ul>

                            <div class="erp-crm-assign-contact">
                                <div class="inner-wrap">
                                    <h4><?php _e( 'Contact Owner', 'wp-erp' ); ?></h4>
                                    <div class="user-wrap">
                                        <?php
                                            $crm_user_id = erp_people_get_meta( $customer->id, '_assign_crm_agent', true );
                                            if ( !empty( $crm_user_id ) ) {
                                                $user        = get_user_by( 'id', $crm_user_id );
                                                $user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')';
                                            }
                                        ?>
                                        <?php if ( $crm_user_id ): ?>
                                            <?php echo erp_crm_get_avatar( $crm_user_id, 32 ); ?>
                                            <div class="user-details">
                                                <a href="#"><?php echo get_the_author_meta( 'display_name', $crm_user_id ); ?></a>
                                                <span><?php echo  get_the_author_meta( 'user_email', $crm_user_id ); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="user-details">
                                                <p><?php _e( 'Nobody', 'wp-erp' ) ?></p>
                                            </div>
                                        <?php endif ?>

                                        <div class="clearfix"></div>

                                        <?php if ( current_user_can( 'erp_crm_edit_contact' ) ): ?>
                                            <span id="erp-crm-edit-assign-contact-to-agent"><i class="fa fa-pencil-square-o"></i></span>
                                        <?php endif ?>
                                    </div>

                                    <div class="assign-form erp-hide">
                                        <form action="" method="post">

                                            <div class="crm-aget-search-select-wrap">
                                                <select name="erp_select_assign_contact" id="erp-select-user-for-assign-contact" style="width: 300px; margin-bottom: 20px;" data-placeholder="<?php _e( 'Search a crm agent', 'wp-erp' ) ?>" data-val="<?php echo $crm_user_id; ?>" data-selected="<?php echo $user_string; ?>">
                                                    <option value=""><?php _e( 'Select a agent', 'wp-erp' ); ?></option>
                                                    <?php if ( $crm_user_id ): ?>
                                                        <option value="<?php echo $crm_user_id ?>" selected><?php echo $user_string; ?></option>
                                                    <?php endif ?>
                                                </select>
                                            </div>

                                            <input type="hidden" name="assign_contact_id" value="<?php echo $customer->id; ?>">
                                            <input type="submit" class="button button-primary save-edit-assign-contact" name="erp_assign_contacts" value="<?php _e( 'Assign', 'wp-erp' ); ?>">
                                            <input type="submit" class="button cancel-edit-assign-contact" value="<?php _e( 'Cancel', 'wp-erp' ); ?>">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .postbox -->

                    <div class="postbox customer-company-info">
                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php echo sprintf( '%s\'s %s', $customer->first_name, __( 'Company', 'wp-erp' ) ); ?></span></h3>
                        <div class="inside company-profile-content">
                            <div class="company-list">
                                <?php $companies = erp_crm_customer_get_company( $customer->id ); ?>
                                <?php foreach ( $companies as $company ) : ?>

                                    <div class="postbox closed">
                                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
                                        <h3 class="erp-hndle">
                                            <span><?php echo $company->company; ?></span>
                                        </h3>
                                        <div class="action">
                                            <!-- <a href="#" class="erp-customer-edit-company" data-id="<?php echo $company->id; ?>" data-action="erp-crm-customer-edit-company"><i class="fa fa-pencil-square-o"></i></a> -->
                                            <a href="#" class="erp-customer-delete-company" data-id="<?php echo $company->id; ?>" data-action="erp-crm-customer-remove-company"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                        <div class="inside company-profile-content">
                                            <ul class="erp-list separated">
                                                <li><?php erp_print_key_value( __( 'Phone', 'wp-erp' ), $company->phone ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Fax', 'wp-erp' ), $company->fax ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Website', 'wp-erp' ), erp_get_clickable( 'url', $company->website ) ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Street 1', 'wp-erp' ), $company->street_1 ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Street 2', 'wp-erp' ), $company->street_2 ); ?></li>
                                                <li><?php erp_print_key_value( __( 'City', 'wp-erp' ), $company->city ); ?></li>
                                                <li><?php erp_print_key_value( __( 'State', 'wp-erp' ), erp_get_state_name( $company->country, $company->state ) ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Country', 'wp-erp' ), erp_get_country_name( $company->country ) ); ?></li>
                                                <li><?php erp_print_key_value( __( 'Postal Code', 'wp-erp' ), $company->postal_code ); ?></li>
                                            </ul>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                                <a href="#" data-id="<?php echo $customer->id; ?>" data-type="assign_company" title="<?php _e( 'Add a company', 'wp-erp' ); ?>" class="button button-primary" id="erp-customer-add-company"><?php _e( '<i class="fa fa-plus"></i> Add a Company', 'wp-erp' ); ?></a>
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

                                <a href="#" id="erp-contact-update-assign-group" data-id="<?php echo $customer->id; ?>" title="<?php _e( 'Assign Contact Groups', 'wp-erp' ); ?>"><i class="fa fa-plus"></i> <?php _e( 'Add any contact groups', 'wp-erp' ); ?></a>
                            </div>
                        </div>
                    </div><!-- .postbox -->

                </div>
            </div>

            <div class="col-4 column-right">
                <?php include WPERP_CRM_VIEWS . '/contact/feeds.php'; ?>
            </div>

        </div>
    </div>

</div>
