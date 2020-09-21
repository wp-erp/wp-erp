<?php
wp_enqueue_script( 'erp-tiptip' );
wp_enqueue_style( 'erp-tiptip' );
$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( wp_unslash( $_REQUEST['tab'] ) ) : 'status';
$tabs        = [
    'status' => __( 'System status', 'erp' ),
];
$tabs        = apply_filters( 'erp_admin_status_tabs', $tabs );
?>
<div class="wrap erp-status">
	<nav class="nav-tab-wrapper">
		<?php
            foreach ( $tabs as $name => $label ) {
                echo '<a href="' . esc_url( admin_url( 'admin.php?page=erp-tools&tab=' . $name ) ) . '" class="nav-tab ';

                if ( $current_tab == $name ) {
                    echo 'nav-tab-active';
                }
                echo '">' . esc_html( $label ) . '</a>';
            }
        ?>
	</nav>
	<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
	<?php
        switch ( $current_tab ) {
            case 'status':
                \WeDevs\ERP\Status::status_report();
        }
    ?>
</div>
