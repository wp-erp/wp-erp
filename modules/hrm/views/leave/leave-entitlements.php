<?php
$cur_year   = date( 'Y' );
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
?>
<div class="wrap" id="wp-erp">

    <h2>
        <?php esc_html_e( 'Leave Entitlements', 'erp' ); ?>
        <?php if ( 'assignment' == $active_tab ): ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements' ) ); ?>" id="erp-new-leave-request" class="add-new-h2"><?php esc_html_e( 'Back to Entitlement list', 'erp' ); ?></a>
        <?php else: ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'sub-section' => 'leave-entitlements', 'tab' => 'assignment' ), admin_url( 'admin.php?page=erp-hr&section=leave' ) ) ); ?>" id="erp-new-leave-request" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
        <?php endif ?>
    </h2>

    <?php if ( 'assignment' == $active_tab ) { ?>

        <p class="description">
            <?php esc_html_e( 'Assign a leave policy to employees.', 'erp' ); ?>
        </p>

        <?php
        $errors = array(
            'invalid-policy'   => __( 'Error: Please select a leave policy.', 'erp' ),
            'invalid-period'   => __( 'Error: Please select a valid period.', 'erp' ),
            'invalid-employee' => __( 'Error: Please select an employee.', 'erp' )
        );

        if ( isset( $_GET['affected' ] ) ) {
            erp_html_show_notice( sprintf( __( '%d Employee(s) has been entitled to this leave policy.', 'erp' ), sanitize_text_field( wp_unslash( $_GET['affected'] ) ) ) );
        }

        if ( isset( $_GET['error'] ) && array_key_exists( sanitize_text_field( wp_unslash( $_GET['error'] ) ), $errors ) ) {
            erp_html_show_notice( $errors[ sanitize_text_field( wp_unslash( $_GET['error'] ) ) ], 'error' );
        }

        $policy_dropdown = erp_hr_leave_get_policies_dropdown_raw();

        if ( empty( $policy_dropdown ) ) {
            $help_text = sprintf( '<a href="?page=erp-hr&section=leave&sub-section=policies">%s</a>', __( 'Create A new policy first', 'erp' ) );
        } else {
            $help_text = __( 'Select A Policy', 'erp' );
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
                'class'    => 'leave-policy-select',
                'tag'      => 'li',
                'required' => true,
                'options'  => array( 0 => __( '- Select -', 'erp' ) ) + erp_hr_leave_get_policies_dropdown_raw(),
                'help'     => $help_text
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Leave Period', 'erp' ),
                'name'     => 'leave_period',
                'type'     => 'select',
                'tag'      => 'li',
                'required' => true,
                'class'    => 'leave-period-select',
                'options'  => erp_hr_leave_period(),
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Employee', 'erp' ),
                'name'     => 'single_employee',
                'type'     => 'select',
                'class'    => 'erp-select2 show-if-single',
                'tag'      => 'li',
                'required' => true,
                'options'  => erp_hr_get_employees_dropdown_raw()
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Location', 'erp' ),
                'name'     => 'location',
                'type'     => 'select',
                'class'    => 'erp-select2 show-if-multiple',
                'tag'      => 'li',
                'options'  => erp_company_get_location_dropdown_raw( __( 'All Locations', 'erp' ) )
            ) );

            erp_html_form_input( array(
                'label'    => __( 'Department', 'erp' ),
                'name'     => 'department',
                'type'     => 'select',
                'class'    => 'erp-select2 show-if-multiple',
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
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="leave">
                    <input type="hidden" name="sub-section" value="leave-entitlements">
                    <?php
                    $entitlement = new \WeDevs\ERP\HRM\Entitlement_List_Table();
                    $entitlement->prepare_items();
                    $entitlement->search_box('Search Employee', 'search');
                    $entitlement->views();

                    $entitlement->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    <?php } ?>
</div>
