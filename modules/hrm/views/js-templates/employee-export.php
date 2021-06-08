<div class="erp-grid-container">
    <div class="row">
        <div class="col-3">
            <label for="fields">
                <h3><?php esc_html_e( 'Select employee fields to export', 'erp' ); ?><span class="required"> *</span></h3>
            </label>
        </div>

        <div class="col-3">
            <h3>
                <input type="checkbox" id="selecctall"/> 
                <strong><?php esc_html_e( 'Select all', 'erp' ); ?></strong>
            </h3>
        </div>
    </div>

    <div class="row" id="fields"></div>
    
    <div class="row"></div>

    <div class="row">
        <p class="description" style="color: grey; float: right;"><?php esc_html_e( '**Only selected fields will be on the csv file.', 'erp' ); ?></p>
    </div>
</div>

<input type="hidden" name="type" value="employee">
<input type="hidden" name="erp_export_csv" value="1">
<?php wp_nonce_field( 'erp-import-export-nonce' ); ?>