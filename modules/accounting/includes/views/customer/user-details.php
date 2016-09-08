<?php $country = \WeDevs\ERP\Countries::instance(); ?>

<ul class="erp-list two-col separated">
    <li><?php erp_print_key_value( __( 'Name', 'erp' ), $customer->get_full_name() ); ?></li>
    <li><?php erp_print_key_value( __( 'Email', 'erp' ), erp_get_clickable( 'email', $customer->get_email() ) ); ?></li>
    <li><?php erp_print_key_value( __( 'Phone', 'erp' ), $customer->phone ); ?></li>
    <li><?php erp_print_key_value( __( 'Mobile', 'erp' ), $customer->mobile ); ?></li>
    <li><?php erp_print_key_value( __( 'Fax', 'erp' ), $customer->fax ); ?></li>
    <li><?php erp_print_key_value( __( 'Website', 'erp' ), $customer->website ); ?></li>
</ul>

<hr>
<strong><?php _e( 'Address', 'erp' ); ?></strong><br>
<?php echo $country->get_formatted_address( [
    'address_1' => $customer->street_1,
    'address_2' => '',
    'city'      => $customer->city,
    'state'     => erp_get_state_name( $customer->country, $customer->state ),
    'postcode'  => $customer->postcode,
    'country'   => erp_get_country_name( $customer->country ),
]); ?>
