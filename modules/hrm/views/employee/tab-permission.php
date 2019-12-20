<div class="permission-tab-wrap">
    <h3><?php esc_html_e( 'Permission Management', 'erp' ) ?></h3>

    <form action="" class="permission-form erp-form" method="post">

        <?php
        $is_manager = user_can( $employee->get_user_id(), erp_hr_get_manager_role() ) ? 'on' : 'off';

        erp_html_form_input( array(
            'label' => __( 'HR Manager', 'erp' ),
            'name'  => 'enable_manager',
            'type'  => 'checkbox',
            'tag'   => 'div',
            'value' => $is_manager,
            'help'  => __( 'This Employee is HR Manager', 'erp'  )
        ) );
        ?>

        <?php do_action( 'erp_hr_permission_management', $employee ); ?>

        <input type="hidden" name="employee_id" value="<?php echo esc_html( $employee->get_user_id() ); ?>">
        <input type="hidden" name="erp-action" id="erp-employee-action" value="erp-hr-employee-permission">

        <?php wp_nonce_field( 'wp-erp-hr-employee-permission-nonce' ); ?>
        <?php submit_button( __( 'Update Permission', 'erp' ), 'primary' ); ?>
    </form>

</div>
