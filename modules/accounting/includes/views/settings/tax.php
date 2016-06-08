<?php
$taxs = erp_ac_get_all_tax([
    'number' => '-1',
    'join'   => ['items', 'ledger']
]);
$charts = erp_ac_get_all_chart( array( 'number' => '-1' ) );
$chart_info = array();

foreach ( $charts as $key => $chart ) {
    $chart_info[$chart->id] = $chart;
}

?>
<tr>
    <td class="erp-ac-tax-td-wrap">
        <div class="erp-ac-setting-tax-wrap">
        	<a data-is_edit="<?php echo '0'; ?>" href="#" class="erp-ac-new-tax-btn page-title-action" id="erp-ac-new-tax-add-btn"><?php _e( 'New Tax Rate', 'accounting'); ?></a>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Name', 'erp' ); ?></th>
                        <th><?php _e( 'Tax Number', 'erp' ); ?></th>
                        <th><?php _e( 'Tax Rate(%)', 'erp' ); ?></th>
                        <th><?php _e( 'Edit', 'erp' ); ?></th>
                        <th><?php _e( 'Delete', 'erp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ( ! $taxs ) {
                    ?>
                    <tr>
                        <td colspan="5"><?php _e( 'No data found!', 'erp' ); ?></td>
                    </tr>
                    <?php
                }

                foreach ( $taxs as $key => $tax ) {

                    $tax_rates = wp_list_pluck( $tax->items, 'tax_rate' );
                    $total_rate = array_sum( $tax_rates );
                    ?>
                    <tr>
                        <td><a data-id="<?php echo $tax->id; ?>" data-items='<?php echo json_encode( $tax->items ); ?>' class="erp-ac-click-tax-details" href="#"><?php echo $tax->name; ?></a></td>
                        <td><?php echo $tax->tax_number; ?></td>
                        <td><?php echo $total_rate; ?></td>
                        <td><a data-is_edit="<?php echo '1'; ?>" data-id="<?php echo $tax->id; ?>" data-items='<?php echo json_encode( $tax ); ?>' class="erp-ac-tax-edit" href="#"><?php _e( 'Edit', 'erp' ); ?></a></td>
                        <td><a data-tax_id="<?php echo $tax->id; ?>" data-items='<?php echo json_encode( $tax ); ?>' class="erp-ac-tax-delete" href="#"><?php _e( 'Delete', 'erp' ); ?></a></td>
                    </tr>
                    <?php
                }

                ?>
                </tbody>
            </table>

        </div>
    </td>
</tr>

<div class="erp-ac-tax-field-clone" style="display: none;">
    <div class="row">
       <?php erp_ac_tax_component_fields();?>
        <span><i class="fa fa-times-circle erp-ac-remove-field"></i></span>
    </div>
</div>