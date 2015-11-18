<div class="erp-customer-form">

    <?php do_action( 'erp-crm-customer-form-top' ); ?>

    <fieldset class="no-border">
        <ol class="form-fields">
            <li>
                <?php erp_html_form_label( __( 'Customer Photo', 'wp-erp' ), 'full-name' ); ?>

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

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Country', 'wp-erp' ),
                    'name'  => 'country',
                    'value' => '{{ data.country }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'State', 'wp-erp' ),
                    'name'  => 'state',
                    'value' => '{{ data.state }}'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Post Code/Zip Code', 'wp-erp' ),
                    'name'  => 'postal_code',
                    'value' => '{{ data.postal_code }}'
                ) ); ?>
            </li>

            <?php do_action( 'erp-hr-employee-form-work' ); ?>

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

            <?php do_action( 'erp-crm-customer-form-personal' ); ?>

        </ol>
    </fieldset>

    <?php do_action( 'erp-crm-customer-form-bottom' ); ?>

    <input type="hidden" name="customer_id" id="erp-customer-id" value="{{ data.id }}">
    <input type="hidden" name="action" id="erp-customer-action" value="erp-crm-customer-new">
    <?php wp_nonce_field( 'wp-erp-crm-customer-nonce' ); ?>

    <?php do_action( 'erp_crm_customer_form' ); ?>
</div>
