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
        <label for="template-body"><?php _e( 'Body', 'erp' ); ?> <span class="required">*</span></label>
        <div class="template-body-wrapper">
            <select name="erp-crm-template-shortcodes" id="erp-crm-template-shortcodes">
                <option value="">--Select--</option>
                <option value="%first_name%">First Name</option>
                <option value="%last_name%">Last Name</option>
                <option value="%full_name%">Full Name</option>
            </select>
            <trix-editor id="template-body" input="template-content" required placeholder="<?php _e( 'Type your content .....', 'erp' ); ?>"></trix-editor>
            <input id="template-content" type="hidden" name="template" required value="{{ data.template }}">
        </div>
    </div>

    <?php wp_nonce_field( 'wp-erp-crm-save-replies' ); ?>

    <input type="hidden" name="action" value="erp-crm-save-replies">
    <input type="hidden" name="id" value="{{ data.id }}">
</div>
