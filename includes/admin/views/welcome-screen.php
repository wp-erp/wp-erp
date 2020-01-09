<div class="wrap about-wrap">
    <?php list( $display_version ) = explode( '-', erp_get_version() ); ?>

    <h1><?php printf( esc_html__( 'Welcome to WP ERP', 'erp' ) ); ?></h1>

    <div class="about-text">
        <?php printf( esc_html__( 'Thank you for installing WP ERP %s!', 'erp' ), esc_html( $display_version ) ); ?>
    </div>

    <h2><?php esc_html_e( 'Getting Started', 'erp' ); ?></h2>

    <ol>
        <li><?php printf( esc_html__( 'Setup %s', 'erp' ), sprintf( '<a target="_blank" href="' . esc_url( admin_url( 'admin.php?page=erp-company' ) ) . '">%s</a>', esc_html__( 'Company Information', 'erp' ) ) ); ?></li>
        <li><?php printf( esc_html__( 'Create %s', 'erp' ), sprintf( '<a target="_blank" href="' . esc_url( admin_url( 'admin.php?page=erp-hr-depts' ) ) . '">%s</a>', esc_html__( 'Departments', 'erp' ) ) ); ?></li>
        <li><?php printf( esc_html__( 'Setup %s', 'erp' ), sprintf( '<a target="_blank" href="' . esc_url( admin_url( 'admin.php?page=erp-hr-designation' ) ) . '">%s</a>', esc_html__( 'Designations', 'erp' ) ) ); ?></li>
        <li><?php printf( esc_html__( 'Create %s', 'erp' ), sprintf( '<a target="_blank" href="' . esc_url( admin_url( 'admin.php?page=erp-hr&section=employee' ) ) . '">%s</a>', esc_html__( 'Employees', 'erp' ) ) ); ?></li>
    </ol>

    <p>&nbsp;</p>

    <a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr' ) ); ?>"><?php esc_html_e( 'Go to HR Dashboard &rarr;', 'erp' ); ?></a>
</div>
