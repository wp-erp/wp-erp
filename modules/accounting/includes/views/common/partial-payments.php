<?php
if ( ! $partial_transaction ) {
	return;
}
?>
<h2><?php _e( 'Related Payments', 'erp' ); ?></h2>
<table class="wp-list-table widefat fixed striped transactions">
    <thead>
        <tr>
            <th><?php _e( 'Date', 'erp' ); ?></th>
            <th><?php _e( 'Ref', 'erp' ); ?></th>
            <th><?php _e( 'Total', 'erp' ); ?></th>
            <th><?php _e( 'Status', 'erp' ); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php
        if ( ! $partial_transaction ) {
            ?>
            <tr>
                <td colspan="4"><?php _e( 'No related payment found!', 'erp' ); ?>
            </tr>
            <?php
        }
        foreach ( $partial_transaction as $key => $partial_trns ) {
            $url        = admin_url( 'admin.php?page=erp-accounting-sales&action=view&id=' . $partial_trns['id'] );
            $issue_date = sprintf( '<a href="%1$s">%2$s</a>', $url, erp_format_date( $partial_trns['issue_date'] ) );

            ?>
            <tr>
                <td><?php echo $issue_date; ?></td>
                <td><?php echo $partial_trns['ref']; ?></td>
                <td><?php echo erp_ac_get_price( $partial_trns['total'] ); ?></td>
                <td><?php echo $partial_trns['status']; ?></td>
            </tr>

            <?php
        }
        ?>
    </tbody>
</table>

