<?php

$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'status';
$tabs        = array(
	'status' => __( 'System status', 'erp' )
);
$tabs        = apply_filters( 'erp_admin_status_tabs', $tabs );
?>
<div class="wrap erp-status">
	<nav class="nav-tab-wrapper">
		<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=erp-status&tab=' . $name ) . '" class="nav-tab ';
				if ( $current_tab == $name ) {
					echo 'nav-tab-active';
				}
				echo '">' . $label . '</a>';
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
