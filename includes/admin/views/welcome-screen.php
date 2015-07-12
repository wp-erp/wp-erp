<div class="wrap about-wrap">
    <?php list( $display_version ) = explode( '-', erp_get_version() ); ?>

    <h1><?php printf( esc_html__( 'Welcome to WP ERP', 'wp-erp' ) ); ?></h1>

    <div class="about-text">
        <?php printf( esc_html__( 'Thank you for installing WP ERP %s!', 'wp-erp' ), $display_version ); ?>
    </div>

    <h2><?php _e( 'Getting Started', 'wp-erp' ); ?></h2>

    <ol>
        <li><?php printf( __( 'Setup %s', 'wp-erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-company' ) . '">%s</a>', __( 'Company Information', 'wp-erp' ) ) ); ?></li>
        <li><?php printf( __( 'Create %s', 'wp-erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-hr-depts' ) . '">%s</a>', __( 'Departments', 'wp-erp' ) ) ); ?></li>
        <li><?php printf( __( 'Setup %s', 'wp-erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-hr-designation' ) . '">%s</a>', __( 'Designations', 'wp-erp' ) ) ); ?></li>
        <li><?php printf( __( 'Create %s', 'wp-erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-hr-employee' ) . '">%s</a>', __( 'Employees', 'wp-erp' ) ) ); ?></li>
    </ol>

    <p>&nbsp;</p>

    <a class="button button-primary button-large" href="<?php echo admin_url( 'admin.php?page=erp-hr' ); ?>"><?php _e( 'Go to HR Dashboard &rarr;', 'wp-erp' ); ?></a>
</div>