<div class="permission-tab-wrap">
    <h3><?php _e( 'Permission Management', 'wp-erp' ) ?></h3>

    <form action="" class="permission-form" method="post">

        <?php erp_html_form_input( array(
            'label' => __( 'Manager', 'wp-erp' ),
            'name'  => 'enable_manager',
            'type'  => 'checkbox',
            'tag'   => 'div',
            'help'  => __( 'This Employee is Manager', 'wp-erp'  )
        ) ); ?>

        <input type="hidden" name="user_id" value="<?php echo $employee->id; ?>">
        <input type="hidden" name="action" id="erp-employee-action" value="erp-hr-employee-permission">

        <?php wp_nonce_field( 'wp-erp-hr-employee-permission-nonce' ); ?>
        <?php submit_button( __( 'Save Changes', 'wp-erp' ), 'primary' ); ?>
    </form>

</div>