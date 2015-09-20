<div class="permission-tab-wrap">
    <h3><?php _e( 'Permission Management', 'wp-erp' ) ?></h3>

    <form action="" class="permission-form erp-form" method="post">

        <?php
        $is_manager = user_can( $employee->id, erp_hr_get_manager_role() ) ? 'on' : 'off';

        erp_html_form_input( array(
            'label' => __( 'Manager', 'wp-erp' ),
            'name'  => 'enable_manager',
            'type'  => 'checkbox',
            'tag'   => 'div',
            'value' => $is_manager,
            'help'  => __( 'This Employee is Manager', 'wp-erp'  )
        ) );
        ?>

        <input type="hidden" name="employee_id" value="<?php echo $employee->id; ?>">
        <input type="hidden" name="erp-action" id="erp-employee-action" value="erp-hr-employee-permission">

        <?php wp_nonce_field( 'wp-erp-hr-employee-permission-nonce' ); ?>
        <?php submit_button( __( 'Update Permission', 'wp-erp' ), 'primary' ); ?>
    </form>

</div>