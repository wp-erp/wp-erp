<div class="erp-ac-new-tax-js-temp-wrap">
    <div class="row">
        <?php erp_html_form_input( array(
            'value' => '{{data.id}}',
            'type'  => 'hidden',
            'name'  => 'id'
        ) ); ?>
    </div>


    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Name', 'erp' ),
            'name'        => 'tax_name',
            'value'       => '{{data.content.name}}',
            'type'        => 'text',
            'required'    => true
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Tax Number', 'erp' ),
            'name'        => 'tax_number', //applicable_accounts[]
            'type'        => 'text',
            'value'       => '{{data.content.tax_number}}'
        ) ); ?>
    </div>

    <div class="erp-ac-multiple-sub-tax-field">
        <h4><?php _e( 'Tax Component', 'erp' ); ?></h4>
        <p>
            <div class="row" data-checkbox="{{data.content.is_compound}}">
                <?php erp_html_form_input( array(
                    'label'       => __( 'Compound', 'erp' ),
                    'name'        => 'compound', //applicable_accounts[]
                    'type'        => 'checkbox',
                    'help'         => __( 'Is this tax compound', 'erp' ),
                    'id'          => 'erp-ac-compound',
                    'class'       => 'erp-ac-checkbox',
                    'value'       => ''
                ) ); ?>
            </div>

        </p>
            <# if ( data.is_edit ) {
                _.each( data.content.items, function( items, index ) {
                    #>
                    <div class="row">
                        <?php erp_ac_tax_component_field_with_value(); ?>
                        <# if ( data.content.items.length > 1 ) { #>
                            <span><i style="" class="fa fa-times-circle erp-ac-remove-field"></i></span>
                        <# } else { #>
                            <span><i style="display: none;" class="fa fa-times-circle erp-ac-remove-field"></i></span>
                        <# } #>

                    </div>
                    <#
                });

            } else { #>
                <div class="row">
                    <?php erp_ac_tax_component_fields(); ?>
                    <span><i style="display: none;" class="fa fa-times-circle erp-ac-remove-field"></i></span>
                </div>
            <# } #>
    </div>
    <# if ( data.content.is_compound === 'on' ) { #>
        <a href="#" class="button-secondary erp-ac-multi-tax-add-more"><?php _e( 'Add More', 'erp' ); ?></a>
    <#  } else { #>
        <a href="#" style="display: none;" class="button-secondary erp-ac-multi-tax-add-more"><?php _e( 'Add More', 'erp' ); ?></a>
    <# } #>



