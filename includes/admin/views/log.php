<div class="wrap">
    <h2><?php _e( 'Audit Log', 'wp-erp' ); ?></h2>

    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">- All Users -</option>
                <option value="1">Tareq Hasan</option>
                <option value="2">Nizam Uddin</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="Filter">
        </div>
    </div>

<?php
$history = array(
    array( 'Dec 11, 1:27pm', 'Tareq Hasan', 'Added <a href="#">Payment</a>', 'Nizam Uddin', '$200'),
    array( 'Dec 11, 1:27pm', 'Tareq Hasan', 'Logged in', '', ''),
    array( 'Dec 11, 1:27pm', 'Tareq Hasan', 'Uploaded company logo', '', ''),
    array( 'Dec 11, 1:27pm', 'Tareq Hasan', 'Added Vendor: <a href="#">Mr Parvez Akhter</a>', '', ''),
);
?>

    <table class="wp-list-table widefat fixed audit-log-table">
        <thead>
            <tr>
                <th class="col-date"><?php _e( 'Date', 'accounting' ); ?></th>
                <th class="col"><?php _e( 'User', 'accounting' ); ?></th>
                <th class="col"><?php _e( 'Event', 'accounting' ); ?></th>
                <th class="col"><?php _e( 'Name', 'accounting' ); ?></th>
                <th class="col-amount"><?php _e( 'Amount', 'accounting' ); ?></th>
                <th class="col-action"><?php _e( 'Action', 'accounting' ); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach( $history as $num => $row ) { ?>
                <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                    <td class="col-date"><?php echo $row[0]; ?></td>
                    <td><?php echo $row[1]; ?></td>
                    <td><?php echo $row[2]; ?></td>
                    <td><?php echo $row[3]; ?></td>
                    <td class="col-amount"><?php echo $row[4]; ?></td>
                    <td class="col-action"><a href="#">View</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>