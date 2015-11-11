<div class="wrap erp erp-hr-leave-request-new erp-hr-leave-reqs-wrap">
    <div class="postbox">
        <h3 class="hndle"><?php _e( 'New Leave Request', 'wp-erp' ); ?></h2>
        <div class="inside">
            <?php if ( isset( $_GET['msg'] ) ) {

                if ( $_GET['msg'] == 'submitted' ) {
                    erp_html_show_notice( __( 'Leave request has been submitted successfully.', 'wp-erp' ) );
                } elseif ( $_GET['msg'] == 'error' ) {
                    erp_html_show_notice( __( 'Something went wrong.', 'wp-erp' ), 'error' );
                }

            } ?>

            <form action="" method="post">

                    <?php if ( current_user_can( 'manage_options' ) ) { ?>
                        <div class="row">
                            <?php erp_html_form_input( array(
                                'label'    => __( 'Employee', 'wp-erp' ),
                                'name'     => 'employee_id',
                                'id'       => 'erp-hr-leave-req-employee-id',
                                'value'    => '',
                                'required' => true,
                                'type'     => 'select',
                                'options'  => erp_hr_get_employees_dropdown_raw()
                            ) ); ?>
                        </div>
                    <?php } ?>

                    <div class="row erp-hide erp-hr-leave-type-wrapper"></div>

                    <div class="row two-col">
                        <div class="cols">
                            <?php erp_html_form_input( array(
                                'label'    => __( 'From', 'wp-erp' ),
                                'name'     => 'leave_from',
                                'id'       => 'erp-hr-leave-req-from-date',
                                'value'    => '',
                                'required' => true,
                                'class'    => 'erp-leave-date-field',
                            ) ); ?>
                        </div>

                        <div class="cols last">
                            <?php erp_html_form_input( array(
                                'label'    => __( 'To', 'wp-erp' ),
                                'name'     => 'leave_to',
                                'id'       => 'erp-hr-leave-req-to-date',
                                'value'    => '',
                                'required' => true,
                                'class'    => 'erp-leave-date-field',
                            ) ); ?>
                        </div>
                    </div>

                    <div class="row erp-hr-leave-req-show-days show-days"></div>

                    <div class="row">
                        <?php erp_html_form_input( array(
                            'label'       => __( 'Reason', 'wp-erp' ),
                            'name'        => 'leave_reason',
                            'type'        => 'textarea',
                            'custom_attr' => array( 'cols' => 30, 'rows' => 3 )
                        ) ); ?>
                    </div>
                </ul>

                <input type="hidden" name="erp-action" value="hr-leave-req-new">
                <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
                <?php submit_button( __( 'Submit Request', 'wp-erp' ), 'primary', 'submit', true, array( 'disabled' => 'disabled' )  ); ?>
            </form>
        </div><!-- .inside-->
    </div><!-- .postbox-->
</div><!-- .wrap -->

<?php erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-days.php', 'erp-leave-days' ); ?>