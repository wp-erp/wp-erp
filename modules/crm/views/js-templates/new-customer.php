<div class="erp-customer-form erp-form">

    <div class="erp-grid-container">

        <div class="row">
            <div class="col-2 left-column">

              <ol class="form-fields">
                <li>
                    <# if ( _.contains( data.types, 'company' ) ) { #>
                        <?php //erp_html_form_label( __( 'Company Photo', 'erp' ), 'company' );?>
                    <# } else { #>
                        <?php //erp_html_form_label( __( 'Contact Photo', 'erp' ), 'full-name' );?>
                    <# } #>
                    <div class="photo-container">
                        <input type="hidden" name="contact[meta][photo_id]" id="customer-photo-id" value="{{ data.avatar.id }}">

                        <# if ( data.avatar.id ) { #>
                            <img src="{{ data.avatar.url }}" alt="Image">
                            <a href="#" class="erp-remove-photo">&times;</a>
                        <# } else { #>
                            <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/mystery-person.png' ); ?>" alt="">
                            <a href="#" id="erp-set-customer-photo" class="button button-primary">
                                <i class="fa fa-cloud-upload"></i>
                                <?php esc_html_e( 'Upload Photo', 'erp' ); ?>
                            </a>
                        <# } #>
                    </div>
                </li>
            </ol>

            </div> <!-- col 2 end -->
            <div class="col-4 right-column">
                <div class="erp-crm-modal-right">
                <# if ( _.contains( data.types, 'company' ) ) { #>
                    <span class="required space-top">* <?php esc_html_e( 'Company name and email are required', 'erp' ); ?></span>

                    <?php do_action( 'erp_crm_company_form_top' ); ?>
                <# } else { #>
                    <span class="required space-top">* <?php esc_html_e( 'First name and email are required', 'erp' ); ?></span>

                    <?php $custom_attr_length = apply_filters( 'erp_crm_custom_attr_length', 30 ); ?>

                    <?php do_action( 'erp_crm_contact_form_top' ); ?>
                <# } #>

                <div class="erp-grid-container">
                    <fieldset class="no-border genaral-info">

                        <div class="row">
                            <# if ( _.contains( data.types, 'contact' ) ) { #>
                                <div class="col-3">
                                    <?php erp_html_form_input( [
                                        'label'       => __( 'First Name', 'erp' ),
                                        'name'        => 'contact[main][first_name]',
                                        'id'          => 'first_name',
                                        'value'       => '{{ data.first_name }}',
                                        'custom_attr' => [ 'maxlength' => $custom_attr_length ],
                                        'required'    => true,
                                    ] ); ?>
                                </div>
                                <div class="col-3">
                                    <?php erp_html_form_input( [
                                        'label'       => __( 'Last Name', 'erp' ),
                                        'name'        => 'contact[main][last_name]',
                                        'id'          => 'last_name',
                                        'value'       => '{{ data.last_name }}',
                                        'custom_attr' => [ 'maxlength' => $custom_attr_length ],
                                    ] ); ?>
                                </div>
                            <# } else if ( _.contains( data.types, 'company' ) ) { #>
                                <div class="col-3 full-width customer-company-name clearfix">
                                    <?php erp_html_form_input( [
                                        'label'       => __( 'Company Name', 'erp' ),
                                        'name'        => 'contact[main][company]',
                                        'id'          => 'company',
                                        'value'       => '{{ data.company }}',
                                        'custom_attr' => [ 'maxlength' => $custom_attr_length ],
                                        'required'    => true,
                                    ] ); ?>
                                </div>
                            <# } #>
                            </div>


                        <div class="row">
                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'       => __( 'Email', 'erp' ),
                                    'name'        => 'contact[main][email]',
                                    'value'       => '{{ data.email }}',
                                    'id'          => 'erp-crm-new-contact-email',
                                    'type'        => 'email',
                                    'required'    => true,
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Phone Number', 'erp' ),
                                    'name'  => 'contact[main][phone]',
                                    'value' => '{{ data.phone }}',
                                ] ); ?>
                            </div>

                            <div class="col-3" data-selected="{{ data.life_stage }}">
                                <?php erp_html_form_input( [
                                    'label'    => __( 'Life Stage', 'erp' ),
                                    'name'     => 'contact[meta][life_stage]',
                                    'required' => true,
                                    'type'     => 'select',
                                    'class'    => 'erp-select2',
                                    'options'  => erp_crm_get_life_stages_dropdown_raw( [ '' => __( '--Select Stage--', 'erp' ) ] ),
                                ] ); ?>
                            </div>

                            <?php if ( current_user_can( 'administrator' ) || current_user_can( 'erp_crm_manager' ) ) { ?>
                                <div class="col-3" data-selected = "{{ data.assign_to.id }}">
                                    <?php erp_html_form_input( [
                                        'label'       => __( 'Contact Owner', 'erp' ),
                                        'name'        => 'contact[meta][contact_owner]',
                                        'required'    => true,
                                        'type'        => 'select',
                                        'id'          => 'erp-crm-contact-owner-id',
                                        'class'       => 'erp-select2 erp-crm-contact-owner-class',
                                        'options'     => erp_crm_get_crm_user_dropdown( [ '' => __( '--Select--', 'erp' ) ] ),
                                    ] ); ?>
                                </div>
                            <?php } elseif ( current_user_can( 'erp_crm_agent' ) ) { ?>
                                <input type="hidden" name="contact[meta][contact_owner]" value="<?php echo esc_attr( get_current_user_id() ); ?>">
                            <?php } ?>

                            <# if ( _.contains( data.types, 'company' ) ) { #>
                                <?php do_action( 'erp_crm_company_form_basic' ); ?>
                            <# } else { #>
                                <?php do_action( 'erp_crm_contact_form_basic' ); ?>
                            <# } #>

                        </div>
                        </fieldset>

                        <p class="advanced-fields">
                            <input type="checkbox" id="advanced_fields">
                            <label for="advanced_fields"><?php esc_html_e( 'Show Advanced Fields', 'erp' ); ?></label>
                        </p>

                        <fieldset class="others-info">
                        <legend><?php esc_attr_e( 'Others Info', 'erp' ); ?></legend>

                        <div class="row">

                            <# if ( _.contains( data.types, 'contact' ) ) { #>
                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Date of Birth', 'erp' ),
                                    'name'  => 'contact[meta][date_of_birth]',
                                    'value' => '{{ data.date_of_birth }}',
                                    'class' => 'erp-crm-date-field',
                                ] ); ?>
                            </div>
                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'       => __( 'Age (years)', 'erp' ),
                                    'name'        => 'contact[meta][contact_age]',
                                    'value'       => '{{ data.contact_age }}',
                                    'class'       => '',
                                    'type'        => 'number',
                                    'custom_attr' => [ 'min' => 1, 'step' => 1 ],
                                ] ); ?>
                            </div>
                            <# } #>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Mobile', 'erp' ),
                                    'name'  => 'contact[main][mobile]',
                                    'value' => '{{ data.mobile }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Website', 'erp' ),
                                    'name'  => 'contact[main][website]',
                                    'value' => '{{ data.website }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Fax Number', 'erp' ),
                                    'name'  => 'contact[main][fax]',
                                    'value' => '{{ data.fax }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Address 1', 'erp' ),
                                    'name'  => 'contact[main][street_1]',
                                    'value' => '{{ data.street_1 }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Address 2', 'erp' ),
                                    'name'  => 'contact[main][street_2]',
                                    'value' => '{{ data.street_2 }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'City', 'erp' ),
                                    'name'  => 'contact[main][city]',
                                    'value' => '{{ data.city }}',
                                ] ); ?>
                            </div>

                            <div class="col-3" data-selected="{{ data.country }}">
                                <label for="erp-popup-country"><?php esc_html_e( 'Country', 'erp' ); ?></label>
                                <select name="contact[main][country]" id="erp-popup-country" class="erp-country-select erp-select2" data-parent="ol">
                                    <?php $country = \WeDevs\ERP\Countries::instance(); ?>
                                    <?php echo wp_kses( $country->country_dropdown( erp_get_country() ),
                                        [
                                            'option' => [
                                                'value'    => [],
                                                'selected' => [],
                                            ],
                                        ] ); ?>
                                </select>
                            </div>

                            <div class="col-3 state-field" data-selected="{{ data.state }}">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Province / State', 'erp' ),
                                    'name'    => 'contact[main][state]',
                                    'id'      => 'erp-state',
                                    'type'    => 'select',
                                    'class'   => 'erp-state-select erp-select2',
                                    'options' => [ '' => __( '- Select -', 'erp' ) ],
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label' => __( 'Post Code/Zip Code', 'erp' ),
                                    'name'  => 'contact[main][postal_code]',
                                    'value' => '{{ data.postal_code }}',
                                ] ); ?>
                            </div>

                            <# if ( _.contains( data.types, 'company' ) ) { #>
                                <?php do_action( 'erp_crm_company_form_other' ); ?>
                            <# } else { #>
                                <?php do_action( 'erp_crm_contact_form_other' ); ?>
                            <# } #>

                        </div>
                        </fieldset>

                    <?php if ( erp_crm_get_contact_group_dropdown() ) { ?>
                        <fieldset class="contact-group">
                            <legend><?php esc_html_e( 'Contact Group', 'erp' ); ?></legend>

                            <div class="row">
                                <div class="col-6" id="erp-crm-contact-subscriber-group-checkbox" data-selected = "{{ data.group_id }}">
                                    <?php erp_html_form_input( [
                                        'label'       => __( 'Assign Group', 'erp' ),
                                        'name'        => 'group_id[]',
                                        'type'        => 'multicheckbox',
                                        'id'          => 'erp-crm-contact-group-id',
                                        'class'       => 'erp-crm-contact-group-class',
                                        'options'     => erp_crm_get_contact_group_dropdown(),
                                    ] ); ?>
                                </div>

                                <# if ( _.contains( data.types, 'company' ) ) { #>
                                    <?php do_action( 'erp_crm_company_form_contact_group' ); ?>
                                <# } else { #>
                                    <?php do_action( 'erp_crm_contact_form_contact_group' ); ?>
                                <# } #>

                            </div>

                        </fieldset>

                        <?php } ?>

                        <fieldset class="additional-info">
                        <legend><?php esc_html_e( 'Additional Info', 'erp' ); ?></legend>

                        <div class="row">

                            <div class="col-3" data-selected="{{ data.source }}">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Contact Source', 'erp' ),
                                    'name'    => 'contact[meta][source]',
                                    'id'      => 'erp-source',
                                    'type'    => 'select',
                                    'class'   => 'erp-source-select',
                                    'options' => erp_crm_contact_sources(),
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Others', 'erp' ),
                                    'name'    => 'contact[main][other]',
                                    'value'   => '{{ data.other }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Notes', 'erp' ),
                                    'name'    => 'contact[main][notes]',
                                    'value'   => '{{ data.notes }}',
                                    'type'    => 'textarea',
                                ] ); ?>
                            </div>

                            <# if ( _.contains( data.types, 'company' ) ) { #>
                                <?php do_action( 'erp_crm_company_form_additional' ); ?>
                            <# } else { #>
                                <?php do_action( 'erp_crm_contact_form_additional' ); ?>
                            <# } #>

                        </div>
                        </fieldset>

                        <fieldset class="social-info">
                        <legend><?php esc_html_e( 'Social Info', 'erp' ); ?></legend>

                        <div class="row">

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Facebook', 'erp' ),
                                    'name'    => 'contact[social][facebook]',
                                    'value'   => '{{ data.social.facebook }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Twitter', 'erp' ),
                                    'name'    => 'contact[social][twitter]',
                                    'value'   => '{{ data.social.twitter }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Google Plus', 'erp' ),
                                    'name'    => 'contact[social][googleplus]',
                                    'value'   => '{{ data.social.googleplus }}',
                                ] ); ?>
                            </div>

                            <div class="col-3">
                                <?php erp_html_form_input( [
                                    'label'   => __( 'Linkedin', 'erp' ),
                                    'name'    => 'contact[social][linkedin]',
                                    'value'   => '{{ data.social.linkedin }}',
                                ] ); ?>
                            </div>

                            <# if ( _.contains( data.types, 'company' ) ) { #>
                                <?php do_action( 'erp_crm_company_form_social' ); ?>
                            <# } else { #>
                                <?php do_action( 'erp_crm_contact_form_social' ); ?>
                            <# } #>

                        </div>
                        </fieldset>

                    </div>

                    <# if ( _.contains( data.types, 'company' ) ) { #>
                    <?php do_action( 'erp_crm_company_form_bottom' ); ?>
                    <# } else { #>
                    <?php do_action( 'erp_crm_contact_form_bottom' ); ?>
                    <# } #>

                    <input type="hidden" name="contact[main][id]" id="erp-customer-id" value="{{ data.id }}">
                    <input type="hidden" name="contact[main][user_id]" id="erp-customer-user-id" value="{{ data.user_id }}">

                    <# if ( _.contains( data.types, 'company' ) ) { #>
                    <input type="hidden" name="contact[main][type]" id="erp-customer-type" value="company">
                    <# } else if ( _.contains( data.types, 'contact' ) ) { #>
                    <input type="hidden" name="contact[main][type]" id="erp-customer-type" value="contact">
                    <# } #>

                    <input type="hidden" name="action" id="erp-customer-action" value="erp-crm-customer-new">
                    <?php wp_nonce_field( 'wp-erp-crm-customer-nonce' ); ?>

                    <# if ( _.contains( data.types, 'company' ) ) { #>
                    <?php do_action( 'erp_crm_company_form' ); ?>
                    <# } else { #>
                    <?php do_action( 'erp_crm_contact_form' ); ?>
                    <# } #>

                    </div>
                    </div>
                    </div>

            </div> <!-- col 4 end -->
        </div>
