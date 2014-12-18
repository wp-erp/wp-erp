<div class="wrap">

    <h2>Designation <a href="#" class="add-new-h2">Add New</a></h2>

<?php
$vendors = array(
    array( 'Senior Developer', 11 ),
    array( 'Junior Developer', 11 ),
    array( 'Marketing Manager', 11 ),
    array( 'Translator', 11 ),
    array( 'Marketing Executive', 11 ),
);

?>

    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">Bulk Actions</option>
                <option value="trash">Move to Trash</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="Apply">
        </div>
    </div>

    <table class="wp-list-table widefat fixed vendor-list-table">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Title', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'No. of Employees', 'accounting' ); ?></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Title', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'No. of Employees', 'accounting' ); ?></th>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php foreach( $vendors as $num => $row ) { ?>
            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <th scope="row" class="check-column">
                    <input id="cb-select-1" type="checkbox" name="post[]" value="1">
                </th>
                <td class="col-">

                    <strong><a href="<?php echo erp_hr_url_single_employee(1); ?>"><?php echo $row[0]; ?></a></strong>

                    <div class="row-actions">
                        <span class="edit"><a href="#" title="Edit this item">Edit</a> | </span>
                        <span class="trash"><a class="submitdelete" title="Delete this item" href="#">Delete</a></span>
                    </div>
                </td>
                <td class="col-"><?php echo isset( $row[1] ) ? $row[1] : '-'; ?></td>
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