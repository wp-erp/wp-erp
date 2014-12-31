<div class="wrap erp-hr-depts">

    <h2><?php _e( 'Departments', 'wp-erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

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

    <table class="wp-list-table widefat fixed department-list-table">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Department', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Department Lead', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'No. of Employees', 'accounting' ); ?></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Department', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Department Lead', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'No. of Employees', 'accounting' ); ?></th>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php

            $departments = erp_hr_get_departments( erp_get_current_company_id() );

            if ( $departments ) {
                foreach( $departments as $num => $row ) {
                    $department = new \WeDevs\ERP\HRM\Department( $row );
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>" id="erp-dept-<?php echo $department->id; ?>">
                        <th scope="row" class="check-column">
                            <input id="cb-select-1" type="checkbox" name="dept[]" value="1">
                        </th>
                        <td class="col-">

                            <strong><a href="#"><?php echo $department->name; ?></a></strong>

                            <div class="row-actions">
                                <span class="edit"><a href="#" title="Edit this item">Edit</a> | </span>
                                <span class="trash"><a class="submitdelete" title="Delete this item" href="#">Delete</a></span>
                            </div>
                        </td>
                        <td class="col-"><?php echo $department->get_lead(); ?></td>
                        <td class="col-"><?php echo $department->num_of_employees(); ?></td>
                    </tr>

                    <?php
                }
            } else {
                ?>
                <tr class="alternate no-rows">
                    <td colspan="4">
                        <?php _e( 'No departments found!', 'wp-erp' ); ?>
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