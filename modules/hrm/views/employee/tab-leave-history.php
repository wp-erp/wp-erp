<?php

if ( ! $requests->isEmpty() ) {

    foreach ($requests as $num => $request) {
        ?>
        <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
            <td>
                <?php
                printf( '%s - %s', erp_format_date( $request->start_date, 'd M' ), erp_format_date( $request->end_date, 'd M' ) );
                ?>
            </td>
            <td><?php echo esc_html( $request->name ); ?></td>
            <td><?php echo !empty( $request->reason ) ? stripslashes($request->reason ) : '-'; ?></td>
            <td><?php echo number_format_i18n( $request->days ); ?></td>
        </tr>

    <?php } ?>

<?php } else { ?>
    <tr class="alternate">
        <td colspan="4">
            <?php _e( 'No history found!', 'erp' ); ?>
        </td>
    </tr>
<?php } ?>
