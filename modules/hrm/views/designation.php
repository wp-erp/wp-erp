<div class="wrap erp erp-hr-designation">

    <h2><?php _e( 'Designation', 'wp-erp' ); ?> <a href="#" id="erp-new-designation" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

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

    <table class="wp-list-table widefat fixed designation-list-table">
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
            <?php

            $designations = erp_hr_get_designations( erp_get_current_company_id() );

            if ( $designations ) {
                foreach( $designations as $num => $row ) {
                    $designation = new \WeDevs\ERP\HRM\Designation( $row );
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <th scope="row" class="check-column">
                            <input id="cb-select-1" type="checkbox" name="post[]" value="1">
                        </th>
                        <td class="col-">

                            <strong><a href="<?php echo ''; ?>"><?php echo esc_html( $designation->name ); ?></a></strong>

                            <div class="row-actions">
                                <span class="edit"><a href="#" data-id="<?php echo $designation->id; ?>" title="Edit this item"><?php _e( 'Edit', 'wp-erp' ); ?></a> | </span>
                                <span class="trash"><a class="submitdelete" data-id="<?php echo $designation->id; ?>" title="Delete this item" href="#"><?php _e( 'Delete', 'wp-erp' ); ?></a></span>
                            </div>
                        </td>
                        <td class="col-"><?php echo $designation->num_of_employees(); ?></td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr class="alternate no-rows">
                    <td colspan="3">
                        <?php _e( 'No designations found!', 'wp-erp' ); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
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