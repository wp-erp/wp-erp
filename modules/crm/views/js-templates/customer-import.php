<?php
    $users          = [];
    $life_stages    = [];
    $groups         = [];
    $type           = '';
    $life_stages    = erp_crm_get_life_stages_dropdown_raw();
    $crm_users      = erp_crm_get_crm_user();
    $section        = ! empty( $_GET['section'] )     ? sanitize_text_field( wp_unslash( $_GET['section'] ) )     : '';
    $sub_section    = ! empty( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : 'contacts';
    $page           = "?page=erp-crm&section={$section}&sub-section={$sub_section}&action=download_sample";

    if ( 'contact' === $section ) {
        $type  = 'companies' === $sub_section ? 'company' : ( 'contacts' === $sub_section ? 'contact' : '' );
    }

    $page          .= "&type={$type}";
    $nonce          = 'erp-import-export-nonce';
    $csv_sample_url = wp_nonce_url( $page, $nonce );

    foreach ( $crm_users as $user ) {
        $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
    }

    $contact_groups = erp_crm_get_contact_groups( [ 'number' => '-1' ] );
    $groups         = [ '' => __( '&mdash; Select Group &mdash;', 'erp' ) ];

    foreach ( $contact_groups as $group ) {
        $groups[ $group->id ] = $group->name;
    }
?>

<div class="notice notice-error erp-error-notice" id="erp-crm-csv-import-error" style="display: none;"></div>

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
                        id="erp-crm-sample-csv"
                        data-url="<?php echo esc_url( $csv_sample_url ); ?>">
                        <?php esc_html_e( 'Download Sample CSV', 'erp' ); ?>
                    </button>
                </p>
            </td>
        </tr>
    </tbody>

    <tbody id="crm_contact_lifestage_owner_wrap">
        <tr>
            <th>
                <label for="contact_owner"><?php esc_html_e( 'Contact Owner', 'erp' ); ?></label>
            </th>
            <td>
                <select name="contact_owner" id="contact_owner">
                    <?php
                    $current_user_id = get_current_user_id();

                    if ( ! erp_crm_is_current_user_manager() && erp_crm_is_current_user_crm_agent() ) {
                        $users = [ $current_user_id => $users[ $current_user_id ] ];
                    }

                    echo wp_kses( erp_html_generate_dropdown( $users, $current_user_id ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Contact owner for contact.', 'erp' ); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="life_stage"><?php esc_html_e( 'Life Stage', 'erp' ); ?></label>
            </th>
            <td>
                <select name="life_stage" id="life_stage">
                    <?php
                    echo wp_kses(
                        erp_html_generate_dropdown( $life_stages ), [
                            'option' => [
                                'value'    => [],
                                'selected' => [],
                            ],
                        ]
                    );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Life stage for contact.', 'erp' ); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="contact_group"><?php esc_html_e( 'Contact Group', 'erp' ); ?></label>
            </th>
            <td>
                <select name="contact_group">
                    <?php
                    echo wp_kses( erp_html_generate_dropdown( $groups ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Imported contacts will be subscribed in selected group.', 'erp' ); ?></p>
            </td>
        </tr>
    </tbody>

    <tbody id="erp-csv-fields-container" style="display: none;"></tbody>
</table>

<input type="hidden" name="type" value="{{ data.type }}">
<input type="hidden" name="action" value="erp_import_csv">
<?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
