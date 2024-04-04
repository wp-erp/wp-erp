<?php
if (
    ! current_user_can( erp_crm_get_manager_role() ) &&
    ! current_user_can( 'manage_options' ) &&
    intval( $customer->contact_owner ) !== get_current_user_id()
) {
    wp_die( esc_html__( 'Unauthorized request.', 'erp' ), 401 );
}

$contact_tags = wp_get_object_terms( $customer->id, 'erp_crm_tag', ['orderby' => 'name', 'order' => 'ASC'] );
$contact_tags = wp_list_pluck( $contact_tags, 'name' );
$trashed      = erp_is_people_trashed( $customer->id );
?>
<div class="wrap erp erp-crm-customer erp-single-customer" id="wp-erp">

    <h2>
        <?php
        esc_attr_e( 'Company #', 'erp' );
        echo esc_attr( $customer->id );
        ?>
        <a href="<?php echo esc_url_raw( add_query_arg( [ 'page' => 'erp-crm', 'section' => 'contact', 'sub-section' => 'companies' ], admin_url( 'admin.php' ) ) ); ?>" id="erp-contact-list" class="add-new-h2"><?php esc_html_e( 'Back to Company list', 'erp' ); ?></a>

        <?php if ( ! $trashed ) : ?>
            <?php if ( current_user_can( 'erp_crm_edit_contact', $customer->id ) || current_user_can( erp_crm_get_manager_role() ) ) : ?>
                <span class="edit">
                    <a href="#" @click.prevent="editContact( 'company', '<?php echo esc_attr( $customer->id ); ?>', '<?php esc_attr_e( 'Edit this company', 'erp' ); ?>' )" data-id="<?php echo esc_attr( $customer->id ); ?>" data-single_view="1" title="<?php esc_attr_e( 'Edit this Company', 'erp' ); ?>" class="add-new-h2"><?php esc_html_e( 'Edit this Company', 'erp' ); ?></a>
                </span>
                <?php if ( ! $customer->user_id && erp_crm_current_user_can_make_wp_user() ) : ?>
                    <span class="make-wp-user">
                        <a href="#" @click.prevent="makeWPUser( 'company', '<?php echo esc_attr( $customer->id ); ?>', '<?php esc_attr_e( 'Make WP User', 'erp' ); ?>', '<?php echo esc_attr( $customer->email ); ?>' )" data-single_view="1" title="<?php esc_attr_e( 'Make this contact as a WP User', 'erp' ); ?>" class="add-new-h2"><?php esc_html_e( 'Make WP User', 'erp' ); ?></a>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </h2>

    <div class="erp-grid-container erp-single-customer-content">
        <div class="row">

            <div class="col-2 column-left erp-single-customer-row">

                <div class="left-content">

                    <div class="customer-image-wraper">
                        <div class="row">
                            <div class="col-2 avatar">
                                <?php echo wp_kses_post( $customer->get_avatar( 100 ) ); ?>
                            </div>
                            <div class="col-4 details">
                                <h3><?php echo esc_html( $customer->get_full_name() ); ?></h3>

                                <?php if ( $customer->get_email() ) { ?>
                                    <p>
                                        <i class="fa fa-envelope"></i>&nbsp;
                                        <?php echo wp_kses_post( erp_get_clickable( 'email', $customer->get_email() ) ); ?>
                                    </p>
                                <?php } ?>

                                <?php if ( $customer->get_mobile() !== '—' ) { ?>
                                    <p>
                                        <i class="fa fa-phone"></i>&nbsp;
                                        <?php echo wp_kses_post( $customer->get_mobile() ); ?>
                                    </p>
                                <?php } ?>

                                <ul class="erp-list list-inline social-profile">
                                    <?php $social_field = erp_crm_get_social_field(); ?>

                                    <?php foreach ( $social_field as $social_key => $social_value ) { ?>
                                        <?php $social_field_data = $customer->get_meta( $social_key, true ); ?>

                                        <?php if ( ! empty( $social_field_data ) ) { ?>
                                            <li><a href="<?php echo esc_url_raw( $social_field_data ); ?>"><?php echo wp_kses_post( $social_value['icon'] ); ?></a></li>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php do_action( 'erp_crm_company_social_fields', $customer ); ?>
                                </ul>

                            </div>
                        </div>
                    </div>

                    <div class="postbox customer-basic-info">
                        <div class="erp-handlediv" title="<?php esc_attr_e( 'Click to toggle', 'erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php esc_html_e( 'Basic Info', 'erp' ); ?></span></h3>
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
                                    <h4><?php esc_html_e( 'Contact Owner', 'erp' ); ?></h4>
                                    <div class="user-wrap">
                                        <div class="user-wrap-content">
                                            <?php
                                            $crm_user_id = $customer->get_contact_owner();

                                            if ( ! empty( $crm_user_id ) ) {
                                                $user        = get_user_by( 'id', $crm_user_id );
                                                $user_string = esc_html( $user->display_name );
                                                $user_email  = $user->get( 'user_email' );
                                            } else {
                                                $user_string = '';
                                            }
                                            ?>
                                            <?php if ( $crm_user_id && ! empty( $user ) ) { ?>
                                                <?php echo wp_kses_post( erp_crm_get_avatar( $crm_user_id, $user_email, $crm_user_id, 32 ) ); ?>
                                                <div class="user-details">
                                                    <a href="#"><?php echo esc_html( get_the_author_meta( 'display_name', $crm_user_id ) ); ?></a>
                                                    <span><?php echo esc_html( get_the_author_meta( 'user_email', $crm_user_id ) ); ?></span>
                                                </div>
                                            <?php } else { ?>
                                                <div class="user-details">
                                                    <p><?php esc_html_e( 'Nobody', 'erp' ); ?></p>
                                                </div>
                                            <?php } ?>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>

                                    <?php if ( current_user_can( 'erp_crm_edit_contact' ) ) { ?>
                                        <span @click.prevent="assignContact()" id="erp-crm-edit-assign-contact-to-agent"><i class="fa fa-pencil-square-o"></i></span>
                                    <?php } ?>

                                    <div class="assign-form erp-hide">
                                        <form action="" method="post">

                                            <div class="crm-aget-search-select-wrap">
                                                <select name="erp_select_assign_contact" id="erp-select-user-for-assign-contact" style="width: 300px; margin-bottom: 20px;" data-placeholder="<?php esc_attr_e( 'Search a crm agent', 'erp' ); ?>" data-val="<?php echo esc_attr( $crm_user_id ); ?>" data-selected="<?php echo esc_attr( $user_string ); ?>">
                                                    <option value=""><?php esc_html_e( 'Select a agent', 'erp' ); ?></option>
                                                    <?php if ( $crm_user_id ) { ?>
                                                        <option value="<?php echo esc_attr( $crm_user_id ); ?>" selected><?php echo esc_html( $user_string ); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <input type="hidden" name="assign_contact_id" value="<?php echo esc_attr( $customer->id ); ?>">
                                            <input type="hidden" id="contact_id" name="contact_id" value="<?php echo esc_attr( $customer->id ); ?>">
                                            <input type="submit" @click.prevent="saveAssignContact()" class="button button-primary save-edit-assign-contact" name="erp_assign_contacts" value="<?php esc_attr_e( 'Assign', 'erp' ); ?>">
                                            <input type="submit" @click.prevent="cancelAssignContact()" class="button cancel-edit-assign-contact" value="<?php esc_attr_e( 'Cancel', 'erp' ); ?>">
                                            <span class="erp-loader erp-hide assign-form-loader"></span>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .postbox -->


                    <div class="postbox erp-customer-tag-div" id="tagsdiv-post_tag">
                        <div class="erp-handlediv" title="<?php esc_attr_e( 'Click to toggle', 'erp' ); ?>"><br></div>
                        <h3 class="erp-hndle"><span><?php esc_html_e( 'Tag', 'erp' ); ?></span></h3>
                        <div class="inside">
                            <div class="tagsdiv" id="tagsdiv-erp-crm-tag">
                                <div class="nojs-tags hide-if-js">
                                    <label for="tax-input-post_tag"><?php esc_html_e( 'Add or remove tags', 'erp' ); ?></label>
                                    <p><textarea name="tax_input[erp_crm_tag]" rows="3" cols="20" class="the-tags" id="tax-input-erp_crm_tag" aria-describedby="new-tag-post_tag-desc">
                                            <?php echo esc_html( implode( ',', $contact_tags ) ); ?>
                                        </textarea></p>
                                </div>

                                <div class="jaxtag">
                                    <div class="ajaxtag hide-if-no-js">
                                        <label class="screen-reader-text" for="new-tag-erp-crm-tag"></label>
                                        <p>
                                            <input style="width: 82%;" data-wp-taxonomy="erp_crm_tag" type="text" id="new-tag-erp-crm-tag" name="newtag[erp_crm_tag]" class="newtag form-input-tip" size="16" autocomplete="on" aria-describedby="new-tag-erp-crm-tag-desc" value="" />
                                            <input type="button" id="add-crm-tag" class="button tagadd" value="<?php esc_attr_e( 'Add', 'erp' ); ?>" /></p>
                                    </div>
                                    <p class="howto" id="new-tag-erp-crm-tag-desc"><?php esc_html_e( 'Separate tags with commas', 'erp' ); ?></p>

                                    <p></p>
                                </div>
                                <ul class="tagchecklist" role="list" style="margin-bottom: 0;"></ul>
                            </div>
                        </div>
                    </div>


                    <contact-company-relation
                        :id="<?php echo esc_attr( $customer->id ); ?>"
                        type="company_contacts"
                        add-button-txt="<?php esc_attr_e( 'Assign a contact', 'erp' ); ?>"
                        title="<?php esc_attr_e( 'Contacts', 'erp' ); ?>"
                    ></contact-company-relation>

                    <contact-assign-group
                        :id="<?php echo esc_attr( $customer->id ); ?>"
                        add-button-txt="<?php esc_attr_e( 'Assign Contact Groups', 'erp' ); ?>"
                        title="<?php esc_attr_e( 'Contact Group', 'erp' ); ?>"
                        is-permitted="<?php echo esc_attr( current_user_can( 'erp_crm_edit_contact', $customer->id ) ); ?>"
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
