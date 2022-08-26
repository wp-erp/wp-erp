<div class="wrap erp-hr-holiday-wrap">

    <a href="http://wperp.com/docs/hr/leave-management/creating-and-importing-holidays" class="add-new-h2 pull-left" target="_blank">
        <?php esc_html_e( 'Download Sample CSV', 'erp' ); ?>
    </a>

    <a href="#upload-csv" class="add-new-h2 pull-left" id="erp-hr-import-ical">
        <?php esc_html_e( 'Upload iCal/CSV', 'erp' ); ?>
        <span class="required">*</span>
    </a>

    <span class="erp-loader erp-hide" id="erp-holiday-uploading"></span>

    <div id="holiday-import-hint" class="pull-right"></div>

    <div class="holiday-data-wrap">
        <div id="holiday_import_warning" class="erp-hide">
            <?php esc_html_e( 'Please do not close or refresh the window until the process is finished.', 'erp' ); ?>
            <span class="done_count">0</span> <?php esc_html_e( 'of', 'erp' ); ?> <span class="total_count"></span> <?php esc_html_e( 'holidays imported.', 'erp' ); ?>
        </div>
        <table class="wp-list-table widefat fixed striped" id="erp-hr-holiday-data">
            <thead>
                <tr>
                    <td><?php esc_html_e( 'Title', 'erp' ); ?></td>
                    <td><?php esc_html_e( 'Start Date', 'erp' ); ?></td>
                    <td><?php esc_html_e( 'End Date', 'erp' ); ?></td>
                    <td><?php esc_html_e( 'Duration', 'erp' ); ?></td>
                </tr>
            </thead>

            <tbody>
                <tr><td colspan="4"><?php esc_html_e( 'No file chosen.', 'erp' ); ?></td></tr>
            </tbody>
        </table>
    </div>

    <?php wp_nonce_field( 'erp-leave-holiday-import' ); ?>
    <input type="hidden" name="action" id="erp-hr-holiday-action" value="erp_hr_holiday_import">
</div>

<style>
#erp-hr-holiday-data {
  height: auto;
  max-height: 330px;
}

input:read-only {
  background-color: inherit;
  border: none;
  cursor: pointer;
}

input:read-only:focus {
  border: none;
}

.holiday-data-wrap {
  margin-top: 10px;
  height: 330px;
  max-height: 330px;
  overflow: auto;
  width: 100%;
}

#holiday-import-hint {
  color: #e04a4a;
}

.ui-datepicker{
  z-index: 1000000 !important;
}

#holiday_import_warning {
  color: #e04a4a;
  font-weight: bold;
  padding: 10px 0;
}
</style>
