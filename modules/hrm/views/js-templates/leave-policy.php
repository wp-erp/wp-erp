<div class="policy-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Policy Name', 'wp-erp' ),
            'name'     => 'name',
            'value'    => '{{ data.name }}',
            'required' => true,
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Days', 'wp-erp' ),
            'name'     => 'days',
            'value'    => '{{ data.days }}',
            'required' => true,
            'help'     => __( 'Days in a calendar year.', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Color', 'wp-erp' ),
            'name'     => 'color',
            'value'    => '{{ data.color }}',
            'required' => true,
            'class'    => 'erp-color-picker'
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-leave-policy' ); ?>
    <input type="hidden" name="action" value="erp-hr-leave-policy-create">
    <input type="hidden" name="policy-id" value="0">
</div>