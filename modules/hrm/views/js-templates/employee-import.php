<?php
$page           = '?page=erp-hr&section=people&sub-section=employee&action=download_sample&type=employee';
$nonce          = 'erp-import-export-nonce';
$csv_sample_url = wp_nonce_url( $page, $nonce );
?>

<div class="notice notice-error erp-error-notice" id="erp-employee-csv-import-error" style="display: none;"></div>

<table class="form-table">
    <tbody>
        <tr>
            <th>
                <label for="csv_file"><?php esc_html_e( 'CSV File', 'erp' ); ?> <span class="required">*</span></label>
            </th>
            <td>
                <input type="file" name="csv_file" id="csv_file" required />
                <p class="description">
                    <?php
                    esc_html_e( 'Upload a csv file.', 'erp' );
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo erp_help_tip( esc_html__( 'Make sure CSV meets the sample CSV format exactly.', 'erp' ) );
                    ?>
                </p>
                <p id="download_sample_wrap">
                    <button class="button button-primary"
                        id="erp-employee-sample-csv"
                        data-url="<?php echo esc_url( $csv_sample_url ); ?>">
                        <?php esc_html_e( 'Download Sample CSV', 'erp' ); ?>
                    </button>
                </p>
            </td>
        </tr>
    </tbody>

    <tbody id="erp-csv-fields-container" style="display: none;"></tbody>
</table>

<input type="hidden" name="type" value="employee">
<input type="hidden" name="action" value="erp_import_csv">
<?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
