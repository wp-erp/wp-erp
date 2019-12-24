<ul class="edit-address">
    <li class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Location Name', 'erp' ),
            'name'     => 'location_name',
            'value'    => '{{ data.name }}',
            'required' => true
        ) ); ?>
    </li>

    <li class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Address Line 1', 'erp' ),
            'name'     => 'address_1',
            'value'    => '{{{ data.address_1 }}}',
            'required' => true
        ) ); ?>
    </li>

    <li class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Address Line 2', 'erp' ),
            'name'     => 'address_2',
            'value'    => '{{ data.address_2 }}',
        ) ); ?>
    </li>

    <li class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'City', 'erp' ),
            'name'     => 'city',
            'value'    => '{{ data.city }}',
        ) ); ?>
    </li>

    <li class="row" data-selected="{{ data.country }}">
        <label for="erp-popup-country"><?php esc_html_e( 'Country', 'erp' ); ?> <span class="required">*</span></label>
        <select name="country" id="erp-popup-country" class="erp-country-select select2" data-parent="ul">
            <?php $country = \WeDevs\ERP\Countries::instance(); ?>
            <?php echo wp_kses( $country->country_dropdown( erp_get_country() ), array(
                'option' => array(
                    'value' => array(),
                    'selected' => array()
                ),
            ) ); ?>
        </select>
    </li>

    <li class="row state-field" data-selected="{{ data.state }}">
        <?php erp_html_form_input( array(
            'label'   => esc_html__( 'Province / State', 'erp' ),
            'name'    => 'state',
            'id'      => 'erp-state',
            'type'    => 'select',
            'class'   => 'erp-state-select',
            'options' => array( 0 => esc_html__( '- Select -', 'erp' ) )
        ) ); ?>
    </li>

    <li class="row">
        <?php erp_html_form_input( array(
            'label'     => __( 'Postal / Zip Code', 'erp' ),
            'name'      => 'zip',
            'type'      => 'text',
            'value'     => '{{ data.zip }}',
        ) ); ?>
    </li>

    <input type="hidden" name="location_id" value="{{ data.id }}">
    <input type="hidden" name="company_id" value="{{ data.company_id }}">
    <input type="hidden" name="action" value="erp-company-location">
    <?php wp_nonce_field( 'erp-company-location' ); ?>
</ul>
