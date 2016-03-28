<div class="erp-customer-form">

    <?php do_action( 'erp-crm-customer-form-top' ); ?>

    <fieldset class="no-border genaral-info">
        <ol class="form-fields">
            <li>
                <# if ( data.type == 'company' ) { #>
                    <?php erp_html_form_label( __( 'Company Photo', 'wp-erp' ), 'company' ); ?>
                <# } else { #>
                    <?php erp_html_form_label( __( 'Contact Photo', 'wp-erp' ), 'full-name' ); ?>
                <# } #>
                <div class="photo-container">
                    <input type="hidden" name="photo_id" id="customer-photo-id" value="{{ data.avatar.id }}">

                    <# if ( data.avatar.id ) { #>
                        <img src="{{ data.avatar.url }}" alt="" />
                        <a href="#" class="erp-remove-photo">&times;</a>
                    <# } else { #>
                        <a href="#" id="erp-set-customer-photo" class="button button-small"><?php _e( 'Upload Customer Photo', 'wp-erp' ); ?></a>
                    <# } #>
                </div>
            </li>

             <# if ( data.type == 'contact' ) { #>
                <li class="full-width name-container clearfix">
                    <?php erp_html_form_label( __( 'Full Name', 'wp-erp' ), 'full-name', true ); ?>

                    <ol class="fields-inline">

                        <li>
                            <?php erp_html_form_input( array(
                                'label'       => __( 'First Name', 'wp-erp' ),
                                'name'        => 'first_name',
                                'id'          => 'first_name',
                                'value'       => '{{ data.first_name }}',
                                'required'    => true,
                                'custom_attr' => array( 'maxlength' => 30 )
                            ) ); ?>
                        </li>
                        <li>
                            <?php erp_html_form_input( array(
                                'label'       => __( 'Last Name', 'wp-erp' ),
                                'name'        => 'last_name',
                                'id'          => 'last_name',
                                'value'       => '{{ data.last_name }}',
                                'required'    => true,
                                'custom_attr' => array( 'maxlength' => 30 )
                            ) ); ?>
                        </li>
                    </ol>
                </li>
            <# } else if ( data.type == 'company' ) { #>
                <li class="full-width customer-company-name clearfix">
                    <?php erp_html_form_input( array(
                        'label'       => __( 'Company Name', 'wp-erp' ),
                        'name'        => 'company',
                        'id'          => 'company',
                        'value'       => '{{ data.company }}',
                        'required'    => true,
                        'custom_attr' => array( 'maxlength' => 30 )
                    ) ); ?>
                </li>
            <# } #>

        </ol>

        <ol class="form-fields two-col">
            <li>
                <?php erp_html_form_input( array(
                    'label'    => __( 'Email', 'wp-erp' ),
                    'name'     => 'email',
                    'value'    => '{{ data.email }}',
                    'required' => true,
                    'type'     => 'email'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Phone Number', 'wp-erp' ),
                    'name'  => 'phone',
                    'value' => '{{ data.phone }}'
                ) ); ?>
            </li>


            <?php do_action( 'erp-crm-customer-form-basic' ); ?>
        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Others Info', 'wp-erp' ) ?></legend>

        <ol class="form-fields two-col">

            <li data-selected="{{ data.life_stage }}">
                <?php erp_html_form_input( array(
                    'label' => __( 'Life Stage', 'wp-erp' ),
                    'name'  => 'life_stage',
                    'required' => true,
                    'type'  => 'select',
                    'class' => 'select2',
                    'options' => erp_crm_get_life_stages_dropdown_raw( [ '' => __( '--Select Stage--', 'wp-erp' ) ] )
                ) ); ?>
            </li>

            <# if ( data.type == 'contact' ) { #>
            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Date of Birth', 'wp-erp' ),
                    'name'  => 'date_of_birth',
                    'value' => '{{ data.date_of_birth }}',
                    'class' => 'erp-crm-date-field'
                ) ); ?>
            </li>
            <# } #>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Mobile', 'wp-erp' ),
                    'name'  => 'mobile',
                    'value' => '{{ data.mobile }}'
                ) ); ?>
            </li>


            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Website', 'wp-erp' ),
                    'name'  => 'website',
                    'value' => '{{ data.website }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Fax Number', 'wp-erp' ),
                    'name'  => 'fax',
                    'value' => '{{ data.fax }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Address 1', 'wp-erp' ),
                    'name'  => 'street_1',
                    'value' => '{{ data.street_1 }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Address 2', 'wp-erp' ),
                    'name'  => 'street_2',
                    'value' => '{{ data.street_2 }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'City', 'wp-erp' ),
                    'name'  => 'city',
                    'value' => '{{ data.city }}'
                ) ); ?>
            </li>

            <li data-selected="{{ data.country }}">
                <label for="erp-popup-country"><?php _e( 'Country', 'wp-erp' ); ?></label>
                <select name="country" id="erp-popup-country" class="erp-country-select select2" data-parent="ol">
                    <?php $country = \WeDevs\ERP\Countries::instance(); ?>
                    <?php echo $country->country_dropdown(); ?>
                </select>
            </li>

            <li data-selected="{{ data.state }}">
                <?php erp_html_form_input( array(
                    'label'   => __( 'Province / State', 'wp-erp' ),
                    'name'    => 'state',
                    'id'      => 'erp-state',
                    'type'    => 'select',
                    'class'   => 'erp-state-select',
                    'options' => array( '' => __( '- Select -', 'wp-erp' ) )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Post Code/Zip Code', 'wp-erp' ),
                    'name'  => 'postal_code',
                    'value' => '{{ data.postal_code }}'
                ) ); ?>
            </li>

            <?php do_action( 'erp-crm-customer-form-work' ); ?>

        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Additional Info', 'wp-erp' ) ?></legend>

        <ol class="form-fields">
            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Notes', 'wp-erp' ),
                    'name'    => 'notes',
                    'value'   => '{{ data.notes }}',
                    'type'   => 'textarea',
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Others', 'wp-erp' ),
                    'name'    => 'other',
                    'value'   => '{{ data.other }}'
                ) ); ?>
            </li>

            <li data-selected="{{ data.source }}">
                <?php erp_html_form_input( array(
                    'label'   => __( 'Contact Source', 'wp-erp' ),
                    'name'    => 'source',
                    'id'      => 'erp-source',
                    'type'    => 'select',
                    'class'   => 'erp-source-select',
                    'options' => erp_crm_contact_sources()
                ) ); ?>
            </li>

            <?php do_action( 'erp-crm-customer-form-personal' ); ?>

        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Social Info', 'wp-erp' ) ?></legend>

        <ol class="form-fields two-col">

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Facebook', 'wp-erp' ),
                    'name'    => 'social[facebook]',
                    'value'   => '{{ data.social.facebook }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Twitter', 'wp-erp' ),
                    'name'    => 'social[twitter]',
                    'value'   => '{{ data.social.twitter }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Google Plus', 'wp-erp' ),
                    'name'    => 'social[googleplus]',
                    'value'   => '{{ data.social.googleplus }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Linkedin', 'wp-erp' ),
                    'name'    => 'social[linkedin]',
                    'value'   => '{{ data.social.linkedin }}'
                ) ); ?>
            </li>

            <?php do_action( 'erp-crm-customer-form-social-profile' ); ?>

        </ol>
    </fieldset>


    <?php do_action( 'erp-crm-customer-form-bottom' ); ?>

    <input type="hidden" name="id" id="erp-customer-id" value="{{ data.id }}">
    <input type="hidden" name="type" id="erp-customer-type" value="{{ data.type }}">
    <input type="hidden" name="action" id="erp-customer-action" value="erp-crm-customer-new">
    <?php wp_nonce_field( 'wp-erp-crm-customer-nonce' ); ?>

    <?php do_action( 'erp_crm_customer_form' ); ?>
</div>
