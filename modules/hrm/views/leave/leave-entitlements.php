<div class="wrap erp-hr-leave-entitlements">
    <h2><?php _e( 'Leave Entitlements', 'wp-erp' ); ?></h2>

    <?php
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
            erp_html_show_notice( sprintf( __( '%d Employees has been entitled to this leave policy.', 'wp-erp' ), $_GET['affected'] ) );
        }

        if ( isset( $_GET['error'] ) && array_key_exists( $_GET['error'], $errors ) ) {
            erp_html_show_notice( $errors[ $_GET['error'] ], 'error' );
        }
        ?>

        <form action="" method="post">

            <ul class="erp-list separated">
            <?php
            $cur_year   = date( 'Y', time() );
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

        listing

    <?php } ?>
</div>