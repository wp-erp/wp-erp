<div class="wrap about-wrap">
    <?php list( $display_version ) = explode( '-', erp_get_version() ); ?>

    <h1><?php printf( esc_html__( 'Welcome to WP ERP', 'erp' ) ); ?></h1>

    <div class="about-text">
        <?php printf( esc_html__( 'Thank you for installing WP ERP %s!', 'erp' ), $display_version ); ?>
    </div>

    <h2><?php _e( 'Getting Started', 'erp' ); ?></h2>

    <ol>
        <li><?php printf( __( 'Setup %s', 'erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-company' ) . '">%s</a>', __( 'Company Information', 'erp' ) ) ); ?></li>
        <li><?php printf( __( 'Create %s', 'erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-hr-depts' ) . '">%s</a>', __( 'Departments', 'erp' ) ) ); ?></li>
        <li><?php printf( __( 'Setup %s', 'erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-hr-designation' ) . '">%s</a>', __( 'Designations', 'erp' ) ) ); ?></li>
        <li><?php printf( __( 'Create %s', 'erp' ), sprintf( '<a target="_blank" href="' . admin_url( 'admin.php?page=erp-hr-employee' ) . '">%s</a>', __( 'Employees', 'erp' ) ) ); ?></li>
    </ol>

    <p>&nbsp;</p>

    <a class="button button-primary button-large" href="<?php echo admin_url( 'admin.php?page=erp-hr' ); ?>"><?php _e( 'Go to HR Dashboard &rarr;', 'erp' ); ?></a>
</div>
