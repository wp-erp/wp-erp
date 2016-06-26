<div class="postbox">
    <div class="inside">
        <h3><?php _e( 'Import CSV', 'erp' ); ?></h3>

        <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>" enctype="multipart/form-data" id="import_form">

            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="type"><?php _e( 'Type', 'erp' ); ?></label>
                        </th>
                        <td>
                            <select name="type" id="type">
                                <?php foreach ( $export_import_types as $key => $value ) { ?>
                                    <option value="<?php echo $key; ?>"><?php _e( $value, 'erp' ); ?></option>
                                <?php } ?>
                            </select>
                            <p class="description"><?php _e( 'Select item type to import.', 'erp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="type"><?php _e( 'CSV File', 'erp' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" />
                            <p class="description"><?php _e( 'Upload a csv file.', 'erp' ); ?></p>
                        </td>
                    </tr>
                </tbody>

                <tbody id="fields_container" style="display: none;">

                </tbody>
            </table>

            <?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
            <?php submit_button( __( 'Import', 'erp' ), 'primary', 'erp_import_csv' ); ?>
        </form>
    </div><!-- .inside -->
</div><!-- .postbox -->
