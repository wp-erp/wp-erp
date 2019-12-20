<div class="erp-crm-save-replies-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Name', 'erp' ),
            'name'        => 'name',
            'type'        => 'text',
            'id'          => 'erp-crm-save-replies-name',
            'required'    => true,
            'value'       => '{{ data.name }}'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Subject', 'erp' ),
            'name'        => 'subject',
            'type'        => 'text',
            'id'          => 'erp-crm-save-replies-subjects',
            'value'       => '{{ data.subject }}',
            'placeholder' => __( 'Optional', 'erp' )
        ) ); ?>
    </div>

    <div class="row">
        <label for="template-body"><?php esc_attr_e( 'Body', 'erp' ); ?> <span class="required">*</span></label>
        <div class="template-body-wrapper">
            <select name="erp-crm-template-shortcodes" id="erp-crm-template-shortcodes">
                <option value="">--Select--</option>
                <?php $shortcodes = erp_crm_get_save_replies_shortcodes(); ?>
                <?php foreach ( $shortcodes as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_attr( $value['title'] ); ?></option>
                <?php endforeach; ?>
            </select>
            <trix-editor id="template-body" input="template-content" required placeholder="<?php esc_attr_e( 'Type your content .....', 'erp' ); ?>"></trix-editor>
            <input id="template-content" type="hidden" name="template" required value="{{ data.template }}">
        </div>
    </div>

    <?php wp_nonce_field( 'wp-erp-crm-save-replies' ); ?>

    <input type="hidden" name="action" value="erp-crm-save-replies">
    <input type="hidden" name="id" value="{{ data.id }}">
</div>
