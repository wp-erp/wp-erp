<div class="wrap erp-hr-leave-requests">

    <h2><?php _e( 'Leave Requests', 'wp-erp' ); ?></h2>

    <ul class="subsubsub">
        <li class="all"><a class="current" href="#">All <span class="count">(2)</span></a> |</li>
        <li class="publish"><a href="#">Pending Approval <span class="count">(2)</span></a> |</li>
        <li class="publish"><a href="#">Taken <span class="count">(2)</span></a> |</li>
        <li class="publish"><a href="#">Rejected <span class="count">(2)</span></a> |</li>
        <li class="publish"><a href="#">Cancelled <span class="count">(2)</span></a> |</li>
        <li class="publish"><a href="#">Scheduled <span class="count">(2)</span></a></li>
    </ul>

    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select bulk action', 'wp-erp' ); ?></label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1" selected="selected">Bulk Actions</option>
                <option value="email">Send Email</option>
                <option value="trash">Move to Trash</option>
            </select>
            <input type="submit" name="" id="doaction" class="button action" value="Apply">
        </div>

        <div class="alignleft actions">
            <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select by job title', 'wp-erp' ); ?></label>
            <select name="job_title" id="erp-job-title">
                <?php echo erp_hr_get_designation_dropdown( erp_get_current_company_id() ) ?>
            </select>

            <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select by department', 'wp-erp' ); ?></label>
            <select name="job_title" id="erp-job-title">
                <?php echo erp_hr_get_departments_dropdown( erp_get_current_company_id() ) ?>
            </select>

            <input type="submit" name="" id="doaction" class="button action" value="<?php _e( 'Filter', 'wp-erp' ); ?>">
        </div>
    </div>

    <table class="wp-list-table widefat fixed erp-employee-list-table">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Name', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Date', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Leave Policy', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Days', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Balance', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Status', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Comments', 'accounting' ); ?></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                    <input id="cb-select-all-1" type="checkbox">
                </th>
                <th class="col-"><?php _e( 'Name', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Date', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Leave Policy', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Days', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Balance', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Status', 'accounting' ); ?></th>
                <th class="col-"><?php _e( 'Comments', 'accounting' ); ?></th>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php
            $employees = erp_hr_get_employees( erp_get_current_company_id() );

            if ( $employees ) {

                foreach( $employees as $num => $employee ) { ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <th scope="row" class="check-column">
                            <input id="cb-select-1" type="checkbox" name="post[]" value="1">
                        </th>
                        <td class="username col- column-username">

                            <strong><a href="<?php echo erp_hr_url_single_employee( $employee->id ); ?>"><?php echo $employee->get_full_name(); ?></a></strong>

                            <div class="row-actions">
                                <span class="edit"><a href="#" data-id="<?php echo $employee->id; ?>" title="<?php echo esc_attr( 'Edit this item', 'wp-erp' ); ?>"><?php _e( 'Edit', 'wp-erp' ); ?></a> | </span>
                                <span class="trash"><a class="submitdelete" data-id="<?php echo $employee->id; ?>" title="<?php echo esc_attr( 'Delete this item', 'wp-erp' ); ?>" href="#"><?php _e( 'Delete', 'wp-erp' ); ?></a></span>
                            </div>
                        </td>
                        <td class="col-"> </td>
                        <td class="col-"> </td>
                        <td class="col-"> </td>
                        <td class="col-"> </td>
                        <td class="col-"> </td>
                        <td class="col-"> </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>

                <tr>
                    <td colspan="6">
                        <?php _e( 'No employees found!', 'wp-erp' ); ?>
                    </td>
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