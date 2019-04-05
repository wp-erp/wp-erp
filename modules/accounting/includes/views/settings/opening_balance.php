<div class="erp-acct-ob-settings-wrap">
    <div class="erp-ac-multiple-ob-field">
        <h4><?php _e('Financial years', 'erp'); ?></h4>
        <div class="erp-ac-ob-field-clone">
            <div class="row">
                <?php erp_html_form_input(array(
                    'label' => __('Name', 'erp'),
                    'name' => 'ob_names[]',
                    'type' => 'text',
                )); ?>
                <?php erp_html_form_input(array(
                    'label' => __('Start Date', 'erp'),
                    'name' => 'ob_starts[]',
                    'type' => 'date',
                )); ?>
                <?php erp_html_form_input(array(
                    'label' => __('End Date', 'erp'),
                    'name' => 'ob_ends[]',
                    'type' => 'date',
                )); ?>
            </div>
        </div>
    </div>
    <a href="#" class="button-secondary erp-ac-ob-add-more"><?php _e('Add More', 'erp'); ?></a>
</div>



