<?php
$cur_year   = date( 'Y' );
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
?>
<div class="wrap erp-hr-employees" id="wp-erp">

    <h2>
        <?php _e( 'Leave Entitlements', 'erp' ); ?>
        <?php if ( 'assignment' == $active_tab ): ?>
            <a href="<?php echo admin_url( 'admin.php?page=erp-leave-assign' ); ?>" id="erp-new-leave-request" class="add-new-h2"><?php _e( 'Back to Entitlement list', 'erp' ); ?></a>
        <?php else: ?>
            <a href="<?php echo add_query_arg( array( 'tab' => 'assignment' ), admin_url( 'admin.php?page=erp-leave-assign' ) ); ?>" id="erp-new-leave-request" class="add-new-h2"><?php _e( 'Add New', 'erp' ); ?></a>
        <?php endif ?>
    </h2>

    <?php if ( 'assignment' == $active_tab ) { ?>

        <p class="description">
            <?php _e( 'Assign a leave policy to employees.', 'erp' ); ?>
        </p>

        <?php
        $errors = array(
            'invalid-policy'   => __( 'Error: Please select a leave policy.', 'erp' ),
            'invalid-period'   => __( 'Error: Please select a valid period.', 'erp' ),
            'invalid-employee' => __( 'Error: Please select an employee.', 'erp' )
        );

        if ( isset( $_GET['affected' ] ) ) {
            erp_html_show_notice( sprintf( __( '%d Employee(s) has been entitled to this leave policy.', 'erp' ), $_GET['affected'] ) );
        }

        if ( isset( $_GET['error'] ) && array_key_exists( $_GET['error'], $errors ) ) {
            erp_html_show_notice( $errors[ $_GET['error'] ], 'error' );
        }
        ?>

        <form action="" method="post">

            <ul class="erp-list separated">
            <?php
            erp_html_form_input( array(
                'label'    => __( 'Assignment', 'erp' ),
                'name'     => 'assignment_to',
                'type'     => 'checkbox',
                'help'     => __( 'Assign to multiple employees', 'erp' ),
                'tag'      => 'li',
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Leave Policy', 'erp' ),
                'name'     => 'leave_policy',
                'type'     => 'select',
                'class'    => 'chosen-select',
                'tag'      => 'li',
                'required' => true,
                'options'  => array( 0 => __( '- Select -', 'erp' ) ) + erp_hr_leave_get_policies_dropdown_raw()
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Leave Period', 'erp' ),
                'name'     => 'leave_period',
                'type'     => 'select',
                'tag'      => 'li',
                'required' => true,
                'class'    => 'chosen-select',
                'options'  => erp_hr_leave_period(),
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Employee', 'erp' ),
                'name'     => 'single_employee',
                'type'     => 'select',
                'class'    => 'chosen-select show-if-single',
                'tag'      => 'li',
                'required' => true,
                'options'  => erp_hr_get_employees_dropdown_raw()
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Location', 'erp' ),
                'name'     => 'location',
                'type'     => 'select',
                'class'    => 'chosen-select show-if-multiple',
                'tag'      => 'li',
                'options'  => erp_company_get_location_dropdown_raw( __( 'All Locations', 'erp' ) )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Department', 'erp' ),
                'name'     => 'department',
                'type'     => 'select',
                'class'    => 'chosen-select show-if-multiple',
                'tag'      => 'li',
                'options'  => erp_hr_get_departments_dropdown_raw( __( 'All Departments', 'erp' ) )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Comment', 'erp' ),
                'name'     => 'comment',
                'type'     => 'textarea',
                'tag'      => 'li',
                'placeholder' => __( 'Optional Comment', 'erp' ),
            ) );

            ?>
            </ul>

            <input type="hidden" name="erp-action" value="hr-leave-assign-policy">

            <?php wp_nonce_field( 'erp-hr-leave-assign' ); ?>
            <?php submit_button( __( 'Assign Policies', 'erp' ), 'primary' ); ?>
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

        <div id="erp-entitlement-table-wrap">

            <div class="list-table-inner">

                <form method="get">
                    <input type="hidden" name="page" value="erp-leave-assign">
                    <input type="hidden" name="tab" value="entitlements">
                    <?php
                    $entitlement = new \WeDevs\ERP\HRM\Entitlement_List_Table();
                    $entitlement->prepare_items();
                    $entitlement->views();

                    $entitlement->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    <?php } ?>
</div>