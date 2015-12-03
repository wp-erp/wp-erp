<?php
$social_field = erp_crm_get_social_field();
?>
<div class="customer-social-wrap">
    <?php foreach ( $social_field as $social_key => $social_value ) : ?>
        <div class="row">
            <?php erp_html_form_input( array(
                'label'       => $social_value['title'],
                'name'        => $social_key,
                'value'       => "{{{ data.social_field.$social_key }}}",
                'id'          => 'erp-customer-social' . $social_key,
                'class'       => 'erp-customer-social' . $social_key,
            ) ); ?>
        </div>
    <?php endforeach; ?>

    <input type="hidden" name="customer_id" id="erp-customer-id" value="{{ data.customer_id }}">
    <input type="hidden" name="action" id="erp-customer-social" value="erp-crm-customer-social">
    <?php wp_nonce_field( 'wp-erp-crm-customer-social-nonce' ); ?>
</div>