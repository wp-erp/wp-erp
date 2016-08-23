<div class="wrap erp erp-crm-customer erp-single-customer" id="wp-erp">

    <h2><?php _e( 'Company #', 'erp' ); echo $customer->id; ?>
        <a href="<?php echo add_query_arg( ['page' => 'erp-sales-companies'], admin_url( 'admin.php' ) ); ?>" id="erp-contact-list" class="add-new-h2"><?php _e( 'Back to Company list', 'erp' ); ?></a>

        <?php if ( current_user_can( 'erp_crm_edit_contact', $customer->id ) ): ?>
            <span class="edit">
                <a href="#" @click.prevent="editContact( 'company', '<?php echo $customer->id; ?>', '<?php _e( 'Edit this company', 'erp' ); ?>' )" data-id="<?php echo $customer->id; ?>" data-single_view="1" title="<?php _e( 'Edit this Company', 'erp' ); ?>" class="add-new-h2"><?php _e( 'Edit this Company', 'erp' ); ?></a>
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
                                    <?php echo erp_get_clickable( 'email', $customer->get_email() ); ?>
                                </p>

                                <?php if ( $customer->get_mobile() != '—' ): ?>
                                    <p>
                                        <i class="fa fa-phone"></i>&nbsp;
                                        <?php echo $customer->get_mobile(); ?>
                                    </p>
                                <?php endif ?>

                                <ul class="erp-list list-inline social-profile">
                                    <?php $social_field = erp_crm_get_social_field(); ?>

                                    <?php foreach ( $social_field as $social_key => $social_value ) : ?>
                                        <?php $social_field_data = $customer->get_meta( $social_key, true ); ?>

                                        <?php if ( ! empty( $social_field_data ) ): ?>
                                            <li><a href="<?php echo esc_url( $social_field_data ); ?>"><?php echo $social_value['icon']; ?></a></li>
                                        <?php endif ?>
                                    <?php endforeach ?>

                                    <?php do_action( 'erp_crm_company_social_fields', $customer ); ?>
                                </ul>

                            </div>
                        </div>
                    </div>

                    <div class="postbox customer-basic-info">
                        <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php _e( 'Basic Info', 'erp' ); ?></span></h3>
                        <div class="inside">
                            <ul class="erp-list separated">
                                <li><?php erp_print_key_value( __( 'Name', 'erp' ), $customer->get_full_name() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Phone', 'erp' ), $customer->get_phone() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Fax', 'erp' ), $customer->get_fax() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Website', 'erp' ), $customer->get_website() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Street 1', 'erp' ), $customer->get_street_1() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Street 2', 'erp' ), $customer->get_street_2() ); ?></li>
                                <li><?php erp_print_key_value( __( 'City', 'erp' ), $customer->get_city() ); ?></li>
                                <li><?php erp_print_key_value( __( 'State', 'erp' ), $customer->get_state() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Country', 'erp' ), $customer->get_country() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Postal Code', 'erp' ), $customer->get_postal_code() ); ?></li>
                                <li><?php erp_print_key_value( __( 'Source', 'erp' ), $customer->get_source() ); ?></li>

                                <?php do_action( 'erp_crm_single_company_basic_info', $customer ); ?>
                            </ul>

                            <div class="erp-crm-assign-contact">
                                <div class="inner-wrap">
                                    <h4><?php _e( 'Contact Owner', 'erp' ); ?></h4>
                                    <div class="user-wrap">
                                        <div class="user-wrap-content">
                                            <?php
                                                $crm_user_id = erp_people_get_meta( $customer->id, '_assign_crm_agent', true );
                                                if ( !empty( $crm_user_id ) ) {
                                                    $user        = get_user_by( 'id', $crm_user_id );
                                                    $user_string = esc_html( $user->display_name );
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
                                                    <p><?php _e( 'Nobody', 'erp' ) ?></p>
                                                </div>
                                            <?php endif ?>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>

                                    <?php if ( current_user_can( 'erp_crm_edit_contact' ) ): ?>
                                        <span @click.prevent="assignContact()" id="erp-crm-edit-assign-contact-to-agent"><i class="fa fa-pencil-square-o"></i></span>
                                    <?php endif ?>

                                    <div class="assign-form erp-hide">
                                        <form action="" method="post">

                                            <div class="crm-aget-search-select-wrap">
                                                <select name="erp_select_assign_contact" id="erp-select-user-for-assign-contact" style="width: 300px; margin-bottom: 20px;" data-placeholder="<?php _e( 'Search a crm agent', 'erp' ) ?>" data-val="<?php echo $crm_user_id; ?>" data-selected="<?php echo $user_string; ?>">
                                                    <option value=""><?php _e( 'Select a agent', 'erp' ); ?></option>
                                                    <?php if ( $crm_user_id ): ?>
                                                        <option value="<?php echo $crm_user_id ?>" selected><?php echo $user_string; ?></option>
                                                    <?php endif ?>
                                                </select>
                                            </div>

                                            <input type="hidden" name="assign_contact_id" value="<?php echo $customer->id; ?>">
                                            <input type="submit" @click.prevent="saveAssignContact()" class="button button-primary save-edit-assign-contact" name="erp_assign_contacts" value="<?php _e( 'Assign', 'erp' ); ?>">
                                            <input type="submit" @click.prevent="cancelAssignContact()" class="button cancel-edit-assign-contact" value="<?php _e( 'Cancel', 'erp' ); ?>">
                                            <span class="erp-loader erp-hide assign-form-loader"></span>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .postbox -->

                    <contact-company-relation
                        :id="<?php echo $customer->id; ?>"
                        type="company_contacts"
                        add-button-txt="<?php _e( 'Assign a contact', 'erp' ) ?>"
                        title="<?php echo sprintf( '%s\'s %s', $customer->get_full_name(), __( 'contacts', 'erp' ) ); ?>"
                    ></contact-company-relation>

                    <contact-assign-group
                        :id="<?php echo $customer->id; ?>"
                        add-button-txt="<?php _e( 'Assign Contact Groups', 'erp' ) ?>"
                        title="<?php _e( 'Contact Group', 'erp' ); ?>"
                        is-permitted="<?php echo current_user_can( 'erp_crm_edit_contact', $customer->id ); ?>"
                    ></contact-assign-group>

                    <?php do_action( 'erp_crm_company_left_widgets', $customer ); ?>

                </div>
            </div>

            <div class="col-4 column-right">
                <?php include WPERP_CRM_VIEWS . '/company/feeds.php'; ?>
            </div>

        </div>
    </div>

</div>
