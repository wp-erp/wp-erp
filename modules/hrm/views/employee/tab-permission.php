<div class="permission-tab-wrap">
    <h3>
        <?php esc_html_e( 'Permission Management', 'erp' ); ?>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo erp_help_tip( esc_html__( 'This is the additional permission for that user if you want to give him/her extra access', 'erp' ) ); ?>
    </h3>

    <form action="" class="permission-form erp-form" method="post">

        <?php
        $is_manager = user_can( $employee->get_user_id(), erp_hr_get_manager_role() ) ? 'on' : 'off';

        erp_html_form_input( [
            'label' => __( 'HR Manager', 'erp' ),
            'name'  => 'enable_manager',
            'type'  => 'checkbox',
            'tag'   => 'div',
            'value' => $is_manager,
            'help'  => __( 'This Employee is HR Manager', 'erp'  ),
        ] );
        ?>

        <?php do_action( 'erp_hr_permission_management', $employee ); ?>

        <input type="hidden" name="employee_id" value="<?php echo esc_attr( $employee->get_user_id() ); ?>">
        <input type="hidden" name="erp-action" id="erp-employee-action" value="erp-hr-employee-permission">

        <?php wp_nonce_field( 'wp-erp-hr-employee-permission-nonce' ); ?>
        <?php submit_button( __( 'Update Permission', 'erp' ), 'primary' ); ?>
    </form>

</div>
