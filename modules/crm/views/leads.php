<div class="wrap">

    <h2>Leads <a href="#" class="add-new-h2">New Lead</a></h2>

<?php
$vendors = array(
    array( 'Parvez Akhter', 'ThemeXpert', '+88017545454', 'parvez@themexpert.com' ),
    array( 'Hasin Hayder', 'Leevio', '+880178745525', 'hasin@leevio.com' ),
    array( 'Nizam Uddin', 'weDevs', '+8801784323695', 'nizam@wedevs.com' ),
    array( 'Kowsher Ahmed', 'Joomshaper', '+8801714785426', 'kowshar@joomshaper.com' ),
    array( 'M Asif Rahman', 'AR Communications', '+880174575155', 'asif@arcom.com.bd' ),
);

?>

    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">Bulk Actions</option>
                <option value="email">Send Email</option>
                <option value="trash">Move to Trash</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="Apply">
        </div>
    </div>

    <table class="wp-list-table widefat fixed leads-list-table">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-username"><?php _e( 'Lead Name', 'accounting' ); ?></th>
                <th class="col-company"><?php _e( 'Company', 'accounting' ); ?></th>
                <th class="col-company"><?php _e( 'Phone', 'accounting' ); ?></th>
                <th class="col-email"><?php _e( 'Email', 'accounting' ); ?></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-username"><?php _e( 'Lead Name', 'accounting' ); ?></th>
                <th class="col-company"><?php _e( 'Company', 'accounting' ); ?></th>
                <th class="col-company"><?php _e( 'Phone', 'accounting' ); ?></th>
                <th class="col-email"><?php _e( 'Email', 'accounting' ); ?></th>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php foreach( $vendors as $num => $row ) { ?>
            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <th scope="row" class="check-column">
                    <input id="cb-select-1" type="checkbox" name="post[]" value="1">
                </th>
                <td class="username col-username column-username">
                    <?php echo get_avatar( $row['2'], 32 ); ?>

                    <strong><a href="<?php echo admin_url( 'admin.php?page=erp-sales-leads&id=1&action=view' ); ?>"><?php echo $row[0]; ?></a></strong>

                    <div class="row-actions">
                        <span class="edit"><a href="#" title="Edit this item">Edit</a> | </span>
                        <span class="trash"><a class="submitdelete" title="Delete this item" href="#">Delete</a></span>
                    </div>
                </td>
                <td class="col-company"><?php echo $row[1]; ?></td>
                <td class="col-email"><?php echo isset( $row[2] ) ? '<a href="tel:' . $row[2] . '">'. $row['2'] . '</a>' : ''; ?></td>
                <td class="col-email"><?php echo isset( $row[3] ) ? '<a href="mailto:' . $row[3] . '">'. $row['3'] . '</a>' : ''; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">Bulk Actions</option>
                <option value="trash">Move to Trash</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="Apply">
        </div>
    </div>

</div>