<?php
    if( isset( $_GET['page'] ) && $_GET['page'] == 'erp-accounting-customers' ) {
        $message = __( 'User already exists. Do you want to import as a customer?', 'accounting' );
        $user    = __('Import Customer', 'accounting');
        $type    = 'customer';
    } else {
        $message = __( 'User already exists. Do you want to import as a vendor?', 'accounting' );
        $user    = __( 'Import Vendor', 'accounting' );
        $type    = 'vendor';
    }

    if ( isset( $_GET['status'] ) && $_GET['status'] == 'new' ) {
        ?>
        <div class="updated notice notice-success is-dismissible">
            <p><?php printf( '%s %s', ucfirst($type), __( 'create successfull', 'accounting' ) ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
        <?php
    }
?>

<div id="message" class="info notice notice-info" style="display: none;">
    <p>
        <span class="erp-ac-user-exsitance-notice"><?php echo $message; ?></span>
        <a href="#" data-status="1" class="erp-ac-convert-user-info" data-people_id="" data-type="<?php echo $type; ?>"><?php echo $user; ?></a>
        <span class="erp-loader" style="padding-left: 40px; display: none;"></span>
    </p>
</div>

<ul class="erp-form-fields erp-list">
    <li class="erp-form-field row-first-name">
        <?php erp_html_form_input( array(
            'label'       => __( 'First Name', 'accounting' ),
            'name'        => 'first_name',
            'id'          => 'first_name',
            'required'    => true,
            'type'        => 'text',
            'placeholder' => __( 'John', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->first_name ) ? $item->first_name : ''
        ) ); ?>
    </li>
    <li class="erp-form-field row-last-name">
        <?php erp_html_form_input( array(
            'label'       => __( 'Last Name', 'accounting' ),
            'name'        => 'last_name',
            'id'          => 'last_name',
            'required'    => true,
            'type'        => 'text',
            'placeholder' => __( 'Doe', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->last_name ) ? $item->last_name : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-email">
        <?php erp_html_form_input( array(
            'label'       => __( 'Email', 'accounting' ),
            'name'        => 'email',
            'id'          => 'email',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'you@domain.com', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->email ) ? $item->email : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-company">
        <?php erp_html_form_input( array(
            'label'       => __( 'Compnay', 'accounting' ),
            'name'        => 'company',
            'id'          => 'company',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'ABC Corporation', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->company ) ? $item->company : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-phone">
        <?php erp_html_form_input( array(
            'label'       => __( 'Phone', 'accounting' ),
            'name'        => 'phone',
            'id'          => 'phone',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( '(541) 754-3010', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->phone ) ? $item->phone : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-mobile">
        <?php erp_html_form_input( array(
            'label'    => __( 'Mobile', 'accounting' ),
            'name'     => 'mobile',
            'id'       => 'mobile',
            'required' => false,
            'type'     => 'text',
            'class'    => 'regular-text',
            'value'    => isset( $item->mobile ) ? $item->mobile : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-other">
        <?php erp_html_form_input( array(
            'label'    => __( 'Other', 'accounting' ),
            'name'     => 'other',
            'id'       => 'other',
            'required' => false,
            'type'     => 'text',
            'class'    => 'regular-text',
            'value'    => isset( $item->other ) ? $item->other : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-website">
        <?php erp_html_form_input( array(
            'label'       => __( 'Website', 'accounting' ),
            'name'        => 'website',
            'id'          => 'website',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'http://domain.com', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->website ) ? $item->website : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-fax">
        <?php erp_html_form_input( array(
            'label'    => __( 'Fax', 'accounting' ),
            'name'     => 'fax',
            'id'       => 'fax',
            'required' => false,
            'type'     => 'text',
            'class'    => 'regular-text',
            'value'    => isset( $item->fax ) ? $item->fax : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-notes">
        <?php erp_html_form_input( array(
            'label'       => __( 'Notes', 'accounting' ),
            'name'        => 'notes',
            'id'          => 'notes',
            'required'    => false,
            'type'        => 'textarea',
            'placeholder' => __( 'Some information about this user', 'accounting' ),
            'custom_attr' => array( 'rows' => 5, 'cols' => 30 ),
            'value'       => isset( $item->notes ) ? $item->notes : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-street-1">
        <?php erp_html_form_input( array(
            'label'       => __( 'Street 1', 'accounting' ),
            'name'        => 'street_1',
            'id'          => 'street_1',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'Street 1', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->street_1 ) ? $item->street_1 : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-street-1">
        <?php erp_html_form_input( array(
            'label'       => __( 'Street 2', 'accounting' ),
            'name'        => 'street_2',
            'id'          => 'street_2',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'Street 2', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->street_1 ) ? $item->street_2 : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-city">
        <?php erp_html_form_input( array(
            'label'       => __( 'City', 'accounting' ),
            'name'        => 'city',
            'id'          => 'city',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'City/Town', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->city ) ? $item->city : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-state">
        <?php erp_html_form_input( array(
            'label'       => __( 'State', 'accounting' ),
            'name'        => 'state',
            'id'          => 'state',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'State/Province', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->state ) ? $item->state : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-postal-code">
        <?php erp_html_form_input( array(
            'label'       => __( 'Post Code', 'accounting' ),
            'name'        => 'postal_code',
            'id'          => 'postal_code',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'Postal Code', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->postal_code ) ? $item->postal_code : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-country">
        <?php erp_html_form_input( array(
            'label'       => __( 'Country', 'accounting' ),
            'name'        => 'country',
            'id'          => 'country',
            'required'    => false,
            'type'        => 'text',
            'placeholder' => __( 'Country', 'accounting' ),
            'class'       => 'regular-text',
            'value'       => isset( $item->country ) ? $item->country : '',
        ) ); ?>
    </li>
    <li class="erp-form-field row-currency">
        <?php erp_html_form_input( array(
            'label'       => __( 'User Currency', 'accounting' ),
            'name'        => 'currency',
            'id'          => 'currency',
            'required'    => false,
            'type'        => 'select',
            'options'     => erp_get_currencies(),
            'value'       => isset( $item->currency ) ? $item->currency : '',
        ) ); ?>
    </li>
</ul>