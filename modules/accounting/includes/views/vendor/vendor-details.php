<?php $country = \WeDevs\ERP\Countries::instance(); ?>

<ul class="erp-list two-col separated">
    <li><?php erp_print_key_value( __( 'Name', 'erp-accounting' ), $vendor->get_full_name() ); ?></li>
    <li><?php erp_print_key_value( __( 'Email', 'erp-accounting' ), erp_get_clickable( 'email', $vendor->get_email() ) ); ?></li>
    <li><?php erp_print_key_value( __( 'Phone', 'erp-accounting' ), $vendor->phone ); ?></li>
    <li><?php erp_print_key_value( __( 'Mobile', 'erp-accounting' ), $vendor->mobile ); ?></li>
    <li><?php erp_print_key_value( __( 'Fax', 'erp-accounting' ), $vendor->fax ); ?></li>
    <li><?php erp_print_key_value( __( 'Website', 'erp-accounting' ), $vendor->website ); ?></li>
</ul>

<hr>
<strong><?php _e( 'Address', 'erp-accounting' ); ?></strong><br>
<?php echo $country->get_formatted_address( [
    'address_1' => $vendor->street_1,
    'address_2' => '',
    'city'      => $vendor->city,
    'state'     => erp_get_state_name( $vendor->country, $vendor->state ),
    'postcode'  => $vendor->postcode,
    'country'   => erp_get_country_name( $vendor->country ),
]); ?>
