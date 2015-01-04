<div class="wrap erp-hr-employees">

    <h2><?php _e( 'Employee', 'wp-erp' ); ?> <a href="#" id="erp-employee-new" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

<?php
$vendors = array(
    array( 'Parvez Akhter', 'Software Engineer', 'Engineering', 'Full-Time Permanent', '1 Dec, 2013' ),
    array( 'Hasin Hayder', 'CTO', 'Engineering', 'Full-Time Permanent', '11 Jun, 2010' ),
    array( 'Nizam Uddin', 'Finance Manager', 'Sales', 'Full-Time Contract', '4 Mar, 2013' ),
    array( 'Kowsher Ahmed', 'QA Engineer', 'Engineering', 'internee', '5 Apr, 2013' ),
    array( 'M Asif Rahman', 'Sales Executive', 'Sales', 'Full-time Contract', '6 Nov, 2012' ),
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

    <table class="wp-list-table widefat fixed vendor-list-table">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Name', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Job Title', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Department', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Employment Status', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Joined', 'accounting' ); ?></th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php foreach( $vendors as $num => $row ) { ?>
            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <th scope="row" class="check-column">
                    <input id="cb-select-1" type="checkbox" name="post[]" value="1">
                </th>
                <td class="username col- column-username">
                    <?php echo get_avatar( $row['2'], 32 ); ?>

                    <strong><a href="<?php echo erp_hr_url_single_employee(1); ?>"><?php echo $row[0]; ?></a></strong>

                    <div class="row-actions">
                        <span class="edit"><a href="#" title="Edit this item">Edit</a> | </span>
                        <span class="trash"><a class="submitdelete" title="Delete this item" href="#">Delete</a></span>
                    </div>
                </td>
                <td class="col-"><?php echo $row[1]; ?></td>
                <td class="col-"><?php echo isset( $row[2] ) ? $row[2] : '-'; ?></td>
                <td class="col-"><?php echo isset( $row[3] ) ? $row[3] : '-'; ?></td>
                <td class="col-"><?php echo isset( $row[4] ) ? $row[4] : '-'; ?></td>
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