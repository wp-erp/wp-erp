<div class="wrap erp-hr-leave-entitlements">
    <h2><?php _e( 'Leave Entitlements', 'wp-erp' ); ?></h2>

    <?php
    $cur_year   = date( 'Y' );
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'assignment';
    $tabs = array(
        'assignment'   => __( 'Assignment', 'wp-erp' ),
        'entitlements' => __( 'Entitlements', 'wp-erp' )
    );
    ?>

    <h2 class="nav-tab-wrapper" style="margin-bottom: 15px;">
        <?php foreach ($tabs as $key => $tab) {
            $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
            ?>
            <a href="<?php echo add_query_arg( array( 'tab' => $key ), admin_url( 'admin.php?page=erp-leave-assign' ) ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php echo esc_html( $tab ); ?></a>
        <?php } ?>
    </h2>

    <?php if ( 'assignment' == $active_tab ) { ?>

        <p class="description">
            <?php _e( 'Assign a leave policy to employees.', 'wp-erp' ); ?>
        </p>

        <?php
        $errors = array(
            'invalid-policy'   => __( 'Error: Please select a leave policy.', 'wp-erp' ),
            'invalid-period'   => __( 'Error: Please select a valid period.', 'wp-erp' ),
            'invalid-employee' => __( 'Error: Please select an employee.', 'wp-erp' )
        );

        if ( isset( $_GET['affected' ] ) ) {
            erp_html_show_notice( sprintf( __( '%d Employee(s) has been entitled to this leave policy.', 'wp-erp' ), $_GET['affected'] ) );
        }

        if ( isset( $_GET['error'] ) && array_key_exists( $_GET['error'], $errors ) ) {
            erp_html_show_notice( $errors[ $_GET['error'] ], 'error' );
        }
        ?>

        <form action="" method="post">

            <ul class="erp-list separated">
            <?php
            $company_id = erp_get_current_company_id();

            erp_html_form_input( array(
                'label'    => __( 'Assignment', 'wp-erp' ),
                'name'     => 'assignment_to',
                'type'     => 'checkbox',
                'help'     => __( 'Assign to multiple employees', 'wp-erp' ),
                'tag'      => 'li',
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Leave Policy', 'wp-erp' ),
                'name'     => 'leave_policy',
                'type'     => 'select',
                'class'    => 'chosen-select',
                'tag'      => 'li',
                'required' => true,
                'options'  => array_merge( array( 0 => __( '- Select -', 'wp-erp' ) ), erp_hr_leave_get_policies_dropdown_raw( $company_id ) )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Leave Period', 'wp-erp' ),
                'name'     => 'leave_period',
                'type'     => 'select',
                'tag'      => 'li',
                'required' => true,
                'class'    => 'chosen-select',
                'options'  => array(
                    $cur_year        => sprintf( '%s - %s', erp_format_date( '01-01-' . $cur_year ), erp_format_date( '31-12-' . $cur_year ) ),
                    ($cur_year + 1 ) => sprintf( '%s - %s', erp_format_date( '01-01-' . ( $cur_year + 1 ) ), erp_format_date( '31-12-' . ( $cur_year + 1 ) ) ),
                )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Employee', 'wp-erp' ),
                'name'     => 'single_employee',
                'type'     => 'select',
                'class'    => 'chosen-select show-if-single',
                'tag'      => 'li',
                'options'  => erp_hr_get_employees_dropdown_raw( $company_id )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Location', 'wp-erp' ),
                'name'     => 'location',
                'type'     => 'select',
                'class'    => 'chosen-select show-if-multiple',
                'tag'      => 'li',
                'options'  => erp_company_get_location_dropdown_raw( $company_id, __( 'All Locations', 'wp-erp' ) )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Department', 'wp-erp' ),
                'name'     => 'department',
                'type'     => 'select',
                'class'    => 'chosen-select show-if-multiple',
                'tag'      => 'li',
                'options'  => erp_hr_get_departments_dropdown_raw( $company_id, __( 'All Departments', 'wp-erp' ) )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Comment', 'wp-erp' ),
                'name'     => 'comment',
                'type'     => 'textarea',
                'tag'      => 'li',
                'placeholder' => __( '(optional comment)', 'wp-erp' ),
            ) );

            ?>
            </ul>

            <input type="hidden" name="erp-action" value="hr-leave-assign-policy">

            <?php wp_nonce_field( 'erp-hr-leave-assign' ); ?>
            <?php submit_button( __( 'Assign Policies', 'wp-erp' ), 'primary' ); ?>
        </form>

        <script type="text/javascript">
            jQuery(function($) {
                $( '#assignment_to' ).on('change', function() {
                    if ( $(this).is(':checked') ) {
                        $( '.department_field, .location_field' ).show();
                        $( '.single_employee_field' ).hide();
                    } else {
                        $( '.department_field, .location_field' ).hide();
                        $( '.single_employee_field' ).show();
                    }
                });

                $( '#assignment_to' ).change();
            });
        </script>

    <?php } else { ?>

        <table class="widefat">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                        <input id="cb-select-all-1" type="checkbox">
                    </th>
                    <th><?php _e( 'Employee Name', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Policy', 'wp-erp' ); ?></th>
                    <th><?php _e( 'From', 'wp-erp' ); ?></th>
                    <th><?php _e( 'To', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Days', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Scheduled', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Available', 'wp-erp' ); ?></th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                        <input id="cb-select-all-1" type="checkbox">
                    </th>
                    <th><?php _e( 'Employee Name', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Policy', 'wp-erp' ); ?></th>
                    <th><?php _e( 'From', 'wp-erp' ); ?></th>
                    <th><?php _e( 'To', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Days', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Scheduled', 'wp-erp' ); ?></th>
                    <th><?php _e( 'Available', 'wp-erp' ); ?></th>
                </tr>
            </tfoot>

            <tbody id="the-list">
                <?php
                $entitlements = erp_hr_leave_get_entitlements( $cur_year );

                if ( $entitlements ) {

                    foreach( $entitlements as $num => $entitlement ) { ?>
                        <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <th scope="row" class="check-column">
                                <input id="cb-select-1" type="checkbox" name="id[]" value="<?php echo $entitlement->id; ?>">
                            </th>
                            <td class="col-">

                                <strong><a href="<?php echo erp_hr_url_single_employee( $entitlement->id ); ?>"><?php echo esc_html( $entitlement->employee_name ); ?></a></strong>

                            </td>
                            <td class="col-"><?php echo esc_html( $entitlement->policy_name ); ?></td>
                            <td class="col-"><?php echo erp_format_date( $entitlement->from_date ); ?></td>
                            <td class="col-"><?php echo erp_format_date( $entitlement->to_date ); ?></td>
                            <td class="col-"><?php echo number_format_i18n( $entitlement->days ); ?></td>
                            <td class="col-"></td>
                            <td class="col-"></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>

                    <tr>
                        <td colspan="6">
                            <?php _e( 'No entitlements found!', 'wp-erp' ); ?>
                        </td>
                    </tr>

                <?php } ?>
            </tbody>
        </table>

    <?php } ?>
</div>