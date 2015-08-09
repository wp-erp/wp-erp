<div class="wrap erp-hr-depts">

    <h2><?php _e( 'Departments', 'wp-erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <form action="" method="get">
        <p class="search-box">
            <input type="hidden" name="page" value="erp-hr-depts">
            <label for="post-search-input" class="screen-reader-text">Search Departments:</label>
            <input type="search" value="<?php _admin_search_query(); ?>" name="s" id="post-search-input">
            <?php wp_nonce_field(); ?>
            <input type="submit" value="Search Department" class="button" id="search-submit">
        </p>
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

        <div id="erp-dept-table-wrap">

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
                    $args = [];
                    if ( isset( $_GET['s'] ) ) {
                        $args['s'] = $_GET['s'];
                    }

                    $departments = erp_hr_get_departments( $args );

                    if ( $departments ) {

                        $walker = new \WeDevs\ERP\HRM\Department_Walker();
                        $walker->walk( $departments, 5 );

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
        </div><!-- #erp-dept-table-wrap -->

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
    </form>

</div>