<div class="erp-customer-form">

    <# if ( _.contains( data.types, 'company' ) ) { #>
        <span class="required">* <?php _e( 'Company name or email or phone is required', 'erp' ) ?></span>

        <?php do_action( 'erp_crm_company_form_top' ); ?>
    <# } else { #>
        <span class="required">* <?php _e( 'First name or email or phone is required', 'erp' ); ?></span>

        <?php do_action( 'erp_crm_contact_form_top' ); ?>
    <# } #>

    <fieldset class="no-border genaral-info">
        <ol class="form-fields">
            <li>
                <# if ( _.contains( data.types, 'company' ) ) { #>
                    <?php erp_html_form_label( __( 'Company Photo', 'erp' ), 'company' ); ?>
                <# } else { #>
                    <?php erp_html_form_label( __( 'Contact Photo', 'erp' ), 'full-name' ); ?>
                <# } #>
                <div class="photo-container">
                    <input type="hidden" name="contact[meta][photo_id]" id="customer-photo-id" value="{{ data.avatar.id }}">

                    <# if ( data.avatar.id ) { #>
                        <img src="{{ data.avatar.url }}" alt="" />
                        <a href="#" class="erp-remove-photo">&times;</a>
                    <# } else { #>
                        <a href="#" id="erp-set-customer-photo" class="button button-small"><?php _e( 'Upload Photo', 'erp' ); ?></a>
                    <# } #>
                </div>
            </li>
        </ol>

        <ol class="form-fields two-col">
            <# if ( _.contains( data.types, 'contact' ) ) { #>
                <li>
                    <?php erp_html_form_input( array(
                        'label'       => __( 'First Name', 'erp' ),
                        'name'        => 'contact[main][first_name]',
                        'id'          => 'first_name',
                        'value'       => '{{ data.first_name }}',
                        'custom_attr' => array( 'maxlength' => 30 )
                    ) ); ?>
                </li>
                <li>
                    <?php erp_html_form_input( array(
                        'label'       => __( 'Last Name', 'erp' ),
                        'name'        => 'contact[main][last_name]',
                        'id'          => 'last_name',
                        'value'       => '{{ data.last_name }}',
                        'custom_attr' => array( 'maxlength' => 30 )
                    ) ); ?>
                </li>
            <# } else if ( _.contains( data.types, 'company' ) ) { #>
                <li class="full-width customer-company-name clearfix">
                    <?php erp_html_form_input( array(
                        'label'       => __( 'Company Name', 'erp' ),
                        'name'        => 'contact[main][company]',
                        'id'          => 'company',
                        'value'       => '{{ data.company }}',
                        'custom_attr' => array( 'maxlength' => 30 )
                    ) ); ?>
                </li>
            <# } #>
        </ol>


        <ol class="form-fields two-col">
            <li>
                <?php erp_html_form_input( array(
                    'label'    => __( 'Email', 'erp' ),
                    'name'     => 'contact[main][email]',
                    'value'    => '{{ data.email }}',
                    'id'       => 'erp-crm-new-contact-email',
                    'type'     => 'email'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Phone Number', 'erp' ),
                    'name'  => 'contact[main][phone]',
                    'value' => '{{ data.phone }}'
                ) ); ?>
            </li>

            <li data-selected="{{ data.life_stage }}">
                <?php erp_html_form_input( array(
                    'label' => __( 'Life Stage', 'erp' ),
                    'name'  => 'contact[meta][life_stage]',
                    'required' => true,
                    'type'  => 'select',
                    'class' => 'erp-select2',
                    'options' => erp_crm_get_life_stages_dropdown_raw( [ '' => __( '--Select Stage--', 'erp' ) ] )
                ) ); ?>
            </li>

            <?php if ( current_user_can( 'administrator' ) || current_user_can( 'erp_crm_manager' ) ): ?>
                <li data-selected = "{{ data.assign_to.id }}">
                    <?php erp_html_form_input( array(
                        'label'       => __( 'Contact Owner', 'erp' ),
                        'name'        => 'contact[meta][contact_owner]',
                        'required'    => true,
                        'type'        => 'select',
                        'id'          => 'erp-crm-contact-owner-id',
                        'class'       => 'erp-select2 erp-crm-contact-owner-class',
                        'options'     => erp_crm_get_crm_user_dropdown( [ '' => '--Select--' ] )
                    ) ); ?>
                </li>
            <?php elseif ( current_user_can( 'erp_crm_agent' ) ): ?>
                <input type="hidden" name="contact[meta][contact_owner]" value="<?php echo get_current_user_id(); ?>">
            <?php endif ?>

            <# if ( _.contains( data.types, 'company' ) ) { #>
                <?php do_action( 'erp_crm_company_form_basic' ); ?>
            <# } else { #>
                <?php do_action( 'erp_crm_contact_form_basic' ); ?>
            <# } #>

        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Others Info', 'erp' ) ?></legend>

        <ol class="form-fields two-col">

            <# if ( _.contains( data.types, 'contact' ) ) { #>
            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Date of Birth', 'erp' ),
                    'name'  => 'contact[meta][date_of_birth]',
                    'value' => '{{ data.date_of_birth }}',
                    'class' => 'erp-date-field erp-crm-date-field'
                ) ); ?>
            </li>
            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Age (years)', 'erp' ),
                    'name'  => 'contact[meta][contact_age]',
                    'value' => '{{ data.contact_age }}',
                    'class' => '',
                    'type'  => 'number',
                    'custom_attr' => [ 'min' => 1, 'step' => 1 ]
                ) ); ?>
            </li>
            <# } #>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Mobile', 'erp' ),
                    'name'  => 'contact[main][mobile]',
                    'value' => '{{ data.mobile }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Website', 'erp' ),
                    'name'  => 'contact[main][website]',
                    'value' => '{{ data.website }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Fax Number', 'erp' ),
                    'name'  => 'contact[main][fax]',
                    'value' => '{{ data.fax }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Address 1', 'erp' ),
                    'name'  => 'contact[main][street_1]',
                    'value' => '{{ data.street_1 }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Address 2', 'erp' ),
                    'name'  => 'contact[main][street_2]',
                    'value' => '{{ data.street_2 }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'City', 'erp' ),
                    'name'  => 'contact[main][city]',
                    'value' => '{{ data.city }}'
                ) ); ?>
            </li>

            <li data-selected="{{ data.country }}">
                <label for="erp-popup-country"><?php _e( 'Country', 'erp' ); ?></label>
                <select name="contact[main][country]" id="erp-popup-country" class="erp-country-select erp-select2" data-parent="ol">
                    <?php $country = \WeDevs\ERP\Countries::instance(); ?>
                    <?php echo $country->country_dropdown(); ?>
                </select>
            </li>

            <li data-selected="{{ data.state }}">
                <?php erp_html_form_input( array(
                    'label'   => __( 'Province / State', 'erp' ),
                    'name'    => 'contact[main][state]',
                    'id'      => 'erp-state',
                    'type'    => 'select',
                    'class'   => 'erp-state-select erp-select2',
                    'options' => array( '' => __( '- Select -', 'erp' ) )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Post Code/Zip Code', 'erp' ),
                    'name'  => 'contact[main][postal_code]',
                    'value' => '{{ data.postal_code }}'
                ) ); ?>
            </li>

            <# if ( _.contains( data.types, 'company' ) ) { #>
                <?php do_action( 'erp_crm_company_form_other' ); ?>
            <# } else { #>
                <?php do_action( 'erp_crm_contact_form_other' ); ?>
            <# } #>

        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Contact Group', 'erp' ) ?></legend>

        <ol class="form-fields two-col">
            <li class="row" id="erp-crm-contact-subscriber-group-checkbox" data-selected = "{{ data.group_id }}">
                <?php erp_html_form_input( array(
                    'label'       => __( 'Assign Group', 'erp' ),
                    'name'        => 'group_id[]',
                    'type'        => 'multicheckbox',
                    'id'          => 'erp-crm-contact-group-id',
                    'class'       => 'erp-crm-contact-group-class',
                    'options'     => erp_crm_get_contact_group_dropdown()
                ) ); ?>
            </li>

            <# if ( _.contains( data.types, 'company' ) ) { #>
                <?php do_action( 'erp_crm_company_form_contact_group' ); ?>
            <# } else { #>
                <?php do_action( 'erp_crm_contact_form_contact_group' ); ?>
            <# } #>

        </ol>

    </fieldset>

    <fieldset>
        <legend><?php _e( 'Additional Info', 'erp' ) ?></legend>

        <ol class="form-fields two-col">
            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Notes', 'erp' ),
                    'name'    => 'contact[main][notes]',
                    'value'   => '{{ data.notes }}',
                    'type'   => 'textarea',
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Others', 'erp' ),
                    'name'    => 'contact[main][other]',
                    'value'   => '{{ data.other }}'
                ) ); ?>
            </li>

            <li data-selected="{{ data.source }}">
                <?php erp_html_form_input( array(
                    'label'   => __( 'Contact Source', 'erp' ),
                    'name'    => 'contact[meta][source]',
                    'id'      => 'erp-source',
                    'type'    => 'select',
                    'class'   => 'erp-source-select',
                    'options' => erp_crm_contact_sources()
                ) ); ?>
            </li>

            <# if ( _.contains( data.types, 'company' ) ) { #>
                <?php do_action( 'erp_crm_company_form_additional' ); ?>
            <# } else { #>
                <?php do_action( 'erp_crm_contact_form_additional' ); ?>
            <# } #>

        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Social Info', 'erp' ) ?></legend>

        <ol class="form-fields two-col">

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Facebook', 'erp' ),
                    'name'    => 'contact[social][facebook]',
                    'value'   => '{{ data.social.facebook }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Twitter', 'erp' ),
                    'name'    => 'contact[social][twitter]',
                    'value'   => '{{ data.social.twitter }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Google Plus', 'erp' ),
                    'name'    => 'contact[social][googleplus]',
                    'value'   => '{{ data.social.googleplus }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Linkedin', 'erp' ),
                    'name'    => 'contact[social][linkedin]',
                    'value'   => '{{ data.social.linkedin }}'
                ) ); ?>
            </li>

            <# if ( _.contains( data.types, 'company' ) ) { #>
                <?php do_action( 'erp_crm_company_form_social' ); ?>
            <# } else { #>
                <?php do_action( 'erp_crm_contact_form_social' ); ?>
            <# } #>

        </ol>
    </fieldset>

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
