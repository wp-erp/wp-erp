<?php

// var_dump( $transaction->toArray() );
// $items = $transaction->items->toArray();
// var_dump( $items );
?>
<div class="wrap">

    <div class="transaction-to">
        <ul>
            <li><?php _e( 'Billing Address', 'erp' ); ?>: <?php echo $transaction->billing_address; ?></li>
            <li><?php _e( 'Issue Date', 'erp' ); ?>: <?php echo $transaction->issue_date; ?></li>
            <li><?php _e( 'Summary', 'erp' ); ?>: <?php echo $transaction->summary; ?></li>
        </ul>
    </div>

    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php _e( 'Account', 'erp' ); ?></th>
                <th><?php _e( 'Product', 'erp' ); ?></th>
                <th><?php _e( 'Description', 'erp' ); ?></th>
                <th><?php _e( 'Qty', 'erp' ); ?></th>
                <th><?php _e( 'Unit Price', 'erp' ); ?></th>
                <th><?php _e( 'Discount', 'erp' ); ?></th>
                <th><?php _e( 'Tax', 'erp' ); ?></th>
                <th><?php _e( 'Tax Amount', 'erp' ); ?></th>
                <th><?php _e( 'Amount', 'erp' ); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach( $transaction->items as $item ) { ?>
                <tr>
                    <td><?php echo $item->journal->ledger->name; ?></td>
                    <td><?php echo $item->product_id; ?></td>
                    <td><?php echo esc_html( $item->description ); ?></td>
                    <td><?php echo $item->qty; ?></td>
                    <td><?php echo $item->unit_price; ?></td>
                    <td><?php echo $item->discount; ?></td>
                    <td><?php echo 99; ?></td>
                    <td><?php echo 99; ?></td>
                    <td><?php echo $item->line_total; ?></td>
                </tr>
            <?php } ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="6" class="align-right"><?php _e( 'Total', 'erp' ); ?></th>
                <th><strong><?php echo $transaction->total; ?></strong></th>
            </tr>
        </tfoot>
    </table>
</div>