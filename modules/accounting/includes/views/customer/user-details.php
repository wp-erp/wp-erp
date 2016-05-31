<?php $country = \WeDevs\ERP\Countries::instance(); ?>

<ul class="erp-list two-col separated">
    <li><?php erp_print_key_value( __( 'Name', 'accounting' ), $customer->get_full_name() ); ?></li>
    <li><?php erp_print_key_value( __( 'Email', 'accounting' ), erp_get_clickable( 'email', $customer->get_email() ) ); ?></li>
    <li><?php erp_print_key_value( __( 'Phone', 'accounting' ), $customer->phone ); ?></li>
    <li><?php erp_print_key_value( __( 'Mobile', 'accounting' ), $customer->mobile ); ?></li>
    <li><?php erp_print_key_value( __( 'Fax', 'accounting' ), $customer->fax ); ?></li>
    <li><?php erp_print_key_value( __( 'Website', 'accounting' ), $customer->website ); ?></li>
</ul>

<hr>
<strong><?php _e( 'Address', 'accounting' ); ?></strong><br>
<?php echo $country->get_formatted_address( [
    'address_1' => $customer->street_1,
    'address_2' => '',
    'city'      => $customer->city,
    'state'     => $customer->state,
    'postcode'  => $customer->postcode,
    'country'   => $customer->country,
]); ?>