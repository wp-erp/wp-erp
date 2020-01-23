<div class="dept-form-wrap">
    <div class="row">
        <?php erp_html_form_label( __( 'Department Title', 'erp' ), 'dept-title', true ); ?>

        <span class="field">
            <input type="text" id="dept-title" name="title" value="" required="required">
        </span>
    </div>

    <div class="row">
        <?php erp_html_form_label( __( 'Description', 'erp' ), 'dept-desc' ); ?>

        <span class="field">
            <textarea name="dept-desc" id="dept-desc" rows="2" cols="20" placeholder="<?php esc_html_e( 'Optional', 'erp' ); ?>"></textarea>
        </span>
    </div>

    <div class="row">
        <?php erp_html_form_label( __( 'Department Lead ...', 'erp' ), 'dept-lead' ); ?>

        <span class="field">
            <select name="lead" id="dept-lead">
                <?php echo wp_kses( erp_hr_get_employees_dropdown(), array(
                    'option' => array(
                        'value' => array(),
                        'selected' => array()
                    ),
                ) ); ?>
            </select>
        </span>
    </div>

    <div class="row">
        <?php erp_html_form_label( __( 'Parent Department', 'erp' ), 'parent-dept' ); ?>

        <span class="field">
            <select name="parent" id="dept-parent">
                <?php echo wp_kses( erp_hr_get_departments_dropdown(), array(
                    'option' => array(
                        'value' => array(),
                        'selected' => array()
                    ),
                ) ); ?>
            </select>
        </span>
    </div>

    <?php wp_nonce_field( 'erp-new-dept' ); ?>
    <input type="hidden" name="action" id="dept-action" value="erp-hr-new-dept">
    <input type="hidden" name="dept_id" id="dept-id" value="0">
</div>
