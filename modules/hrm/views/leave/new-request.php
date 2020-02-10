<div class="wrap erp erp-hr-leave-request-new erp-hr-leave-reqs-wrap">
    <div class="postbox">
        <h3 class="hndle"><?php esc_html_e( 'New Leave Request', 'erp' ); ?></h3>
        <div class="inside">
            <?php if ( isset( $_GET['msg'] ) ) {

                if ( $_GET['msg'] == 'submitted' ) {
                    erp_html_show_notice( __( 'Leave request has been submitted successfully.', 'erp' ) );
                } elseif ( $_GET['msg'] == 'error' ) {
                    erp_html_show_notice( __( 'Something went wrong.', 'erp' ), 'error' );
                } elseif ( $_GET['msg'] == 'no_reason' ) {
                    erp_html_show_notice( __( 'Leave reason field can not be blank.', 'erp' ), 'error' );
                }

            } ?>

            <form action="" method="post" enctype="multipart/form-data">

                <?php if ( current_user_can( 'erp_leave_create_request' ) ) { ?>
                    <div class="row">
                        <?php erp_html_form_input( array(
                            'label'    => __( 'Employee', 'erp' ),
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
                            'label'    => __( 'From', 'erp' ),
                            'name'     => 'leave_from',
                            'id'       => 'erp-hr-leave-req-from-date',
                            'value'    => '',
                            'required' => true,
                            'class'    => 'erp-leave-date-field',
                            'custom_attr' => [ 'disabled' => 'disabled' ]
                        ) ); ?>
                    </div>

                    <div class="cols last">
                        <?php erp_html_form_input( array(
                            'label'    => __( 'To', 'erp' ),
                            'name'     => 'leave_to',
                            'id'       => 'erp-hr-leave-req-to-date',
                            'value'    => '',
                            'required' => true,
                            'class'    => 'erp-leave-date-field',
                            'custom_attr' => [ 'disabled' => 'disabled' ]
                        ) ); ?>
                    </div>
                </div>

                <div class="row erp-hr-leave-req-show-days show-days"></div>

                <div class="row">
                    <?php erp_html_form_input( array(
                        'label'       => __( 'Reason', 'erp' ),
                        'name'        => 'leave_reason',
                        'type'        => 'textarea',
                        'required'    => true,
                        'custom_attr' => array( 'cols' => 30, 'rows' => 3, 'disabled' => 'disabled' )
                    ) ); ?>
                </div>

                <div class="row">
                    <label for="leave_document"><?php echo esc_html__( 'Document', 'wp-erp' );?></label>
                    <input type="file" name="leave_document[]" id="leave_document" multiple>
                </div>

                <input type="hidden" name="erp-action" value="hr-leave-req-new">
                <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
                <?php submit_button( __( 'Submit Request', 'erp' ), 'primary', 'submit', true, array( 'disabled' => 'disabled' )  ); ?>

            </form>
        </div><!-- .inside-->
    </div><!-- .postbox-->
</div><!-- .wrap -->

<?php erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-days.php', 'erp-leave-days' ); ?>
