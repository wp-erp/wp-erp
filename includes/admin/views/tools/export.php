<div class="postbox">
    <div class="inside">
        <h3><?php esc_html_e( 'Export CSV', 'erp' ); ?></h3>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=erp-tools' ) ); ?>" id="export_form">

            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="type"><?php esc_html_e( 'Type', 'erp' ); ?></label>
                        </th>
                        <td>
                            <select name="type" id="type">
                                <?php foreach ( $import_export_types as $key => $value ) { ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( $value, 'erp' ); ?></option>
                                <?php } ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select item type to export.', 'erp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="fields"><?php esc_html_e( 'Fields', 'erp' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <label><input type="checkbox" id="selecctall"/> <strong><?php esc_html_e( 'Select All', 'erp' ); ?></strong></label>
                            <br />
                            <div id="fields"></div>
                            <p class="description"><?php esc_html_e( 'Only selected field will be on the csv file.', 'erp' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
            <?php submit_button( esc_html__( 'Export', 'erp' ), 'primary', 'erp_export_csv' ); ?>
        </form>
    </div><!-- .inside -->
</div><!-- .postbox -->
