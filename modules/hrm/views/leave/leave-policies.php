<div class="wrap erp-hr-leave-policy">
    <h2><?php _e( 'Leave Policies', 'wp-erp' ); ?> <a href="#" id="erp-leave-policy-new" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

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
    </div>

    <div class="list-table-wrap">
        <div class="list-wrap-inner">
            <table class="wp-list-table widefat fixed erp-leave-policy-list-table">
                <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th class="col-"><?php _e( 'Policy Name', 'wp-erp' ); ?></th>
                        <th class="col-"><?php _e( 'Leave Days', 'wp-erp' ); ?></th>
                        <th class="col-"><?php _e( 'Calendar Color', 'wp-erp' ); ?></th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th class="col-"><?php _e( 'Policy Name', 'wp-erp' ); ?></th>
                        <th class="col-"><?php _e( 'Leave Days', 'wp-erp' ); ?></th>
                        <th class="col-"><?php _e( 'Calendar Color', 'wp-erp' ); ?></th>
                    </tr>
                </tfoot>

                <tbody id="the-list">
                    <?php
                    $policies = erp_hr_leave_get_policies( erp_get_current_company_id() );

                    if ( $policies ) {

                        foreach( $policies as $num => $policy ) { ?>
                            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>" data-json='<?php echo json_encode( $policy ); ?>'>
                                <th scope="row" class="check-column">
                                    <input id="cb-select-1" type="checkbox" name="policy_id[]" value="<?php echo $policy->id; ?>">
                                </th>
                                <td class="username col-">

                                    <strong><a href="#" class="link" data-id="<?php echo $policy->id; ?>"><?php echo esc_html( $policy->name ); ?></a></strong>

                                    <div class="row-actions">
                                        <span class="edit"><a href="#" data-id="<?php echo $policy->id; ?>" title="<?php echo esc_attr( 'Edit this item', 'wp-erp' ); ?>"><?php _e( 'Edit', 'wp-erp' ); ?></a> | </span>
                                        <span class="trash"><a class="submitdelete" data-id="<?php echo $policy->id; ?>" title="<?php echo esc_attr( 'Delete this item', 'wp-erp' ); ?>" href="#"><?php _e( 'Delete', 'wp-erp' ); ?></a></span>
                                    </div>
                                </td>
                                <td class="col-"><?php echo number_format_i18n( $policy->value, 0 ); ?></td>
                                <td class="col-color"><span class="leave-color" style="background-color: <?php echo $policy->color; ?>"></span></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>

                        <tr>
                            <td colspan="6">
                                <?php _e( 'No policies found!', 'wp-erp' ); ?>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div><!-- .list-wrap-inner -->
    </div><!-- .list-table-wrap -->
</div>