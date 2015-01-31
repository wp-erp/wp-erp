<div class="wrap erp erp-hr-leave-request-new">
    <h2><?php _e( 'New Leave Request', 'wp-erp' ); ?></h2>

    <?php $company_id = erp_get_current_company_id(); ?>

    <?php if ( isset( $_GET['msg'] ) ) {

        if ( $_GET['msg'] == 'submitted' ) {
            erp_html_show_notice( __( 'Leave request has been submitted successfully.', 'wp-erp' ) );
        } elseif ( $_GET['msg'] == 'error' ) {
            erp_html_show_notice( __( 'Something went wrong.', 'wp-erp' ), 'error' );
        }

    } ?>

    <form action="" method="post">

        <ul class="erp-list blur blocked" style="width: 400px;">

            <?php if ( current_user_can( 'manage_options' ) ) { ?>
                <li>
                    <?php erp_html_form_input( array(
                        'label'    => __( 'Employee', 'wp-erp' ),
                        'name'     => 'employee_id',
                        'value'    => '',
                        'required' => true,
                        'type'     => 'select',
                        'options'  => erp_hr_get_employees_dropdown_raw( $company_id )
                    ) ); ?>
                </li>
            <?php } ?>

            <li>
                <?php erp_html_form_input( array(
                    'label'    => __( 'Leave Type', 'wp-erp' ),
                    'name'     => 'leave_policy',
                    'value'    => '',
                    'required' => true,
                    'type'     => 'select',
                    'options'  => array( '' => __( '- Select -', 'wp-erp' ) ) + erp_hr_leave_get_policies_dropdown_raw( $company_id )
                ) ); ?>
            </li>

            <li class="two-col">
                <div class="cols">
                    <?php erp_html_form_input( array(
                        'label'    => __( 'From', 'wp-erp' ),
                        'name'     => 'leave_from',
                        'value'    => '',
                        'required' => true,
                        'class'    => 'erp-date-field',
                    ) ); ?>
                </div>

                <div class="cols last">
                    <?php erp_html_form_input( array(
                        'label'    => __( 'To', 'wp-erp' ),
                        'name'     => 'leave_to',
                        'value'    => '',
                        'required' => true,
                        'class'    => 'erp-date-field',
                    ) ); ?>
                </div>
            </li>

            <li class="show-days"></li>

            <li>
                <?php erp_html_form_input( array(
                    'label'       => __( 'Reason', 'wp-erp' ),
                    'name'        => 'leave_reason',
                    'type'        => 'textarea',
                    'custom_attr' => array( 'cols' => 30, 'rows' => 3 )
                ) ); ?>
            </li>
        </ul>

        <input type="hidden" name="erp-action" value="hr-leave-req-new">
        <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
        <?php submit_button( __( 'Submit Request', 'wp-erp' ), 'primary', 'submit', true, array( 'disabled' => 'disabled' )  ); ?>
    </form>
</div><!-- .wrap -->

<?php erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-days.php', 'erp-leave-days' ); ?>