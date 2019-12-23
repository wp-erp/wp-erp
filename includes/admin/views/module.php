<?php

$tab                = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false;
$modules            = wperp()->modules->get_query_modules( $tab );
$all_active_modules = wperp()->modules->get_active_modules();
$count_all          = count( wperp()->modules->get_modules() );
$count_active       = count( $all_active_modules );
$count_inactive     = count( wperp()->modules->get_inactive_modules() );

$all_url            = admin_url( 'admin.php?page=erp-modules' );
$active_url         = admin_url( 'admin.php?page=erp-modules&tab=active' );
$inactive_url       = admin_url( 'admin.php?page=erp-modules&tab=inactive' );

$current_tab        = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''
?>
<div class="wrap erp-settings">
	<h2><?php esc_html_e( 'Modules', 'erp' ); ?></h2>
	<ul class="erp-subsubsub">
		<li><a class="erp-nav-tab<?php echo esc_attr( $current_tab ) == '' ? ' erp-nav-tab-active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>"><?php esc_html( printf( esc_html__( 'All (%s) |', 'erp' ), esc_html( $count_all ) ) ); ?></a></li>
		<li><a class="erp-nav-tab<?php echo esc_attr( $current_tab ) == 'active' ? ' erp-nav-tab-active' : ''; ?>" href="<?php echo esc_url($active_url); ?>"><?php esc_html( printf( esc_html__( 'Active (%s) |', 'erp' ), esc_html( $count_active ) ) ); ?></a></li>
		<li><a class="erp-nav-tab<?php echo esc_attr( $current_tab ) == 'inactive' ? ' erp-nav-tab-active' : ''; ?>" href="<?php echo  esc_url( $inactive_url ); ?>"><?php esc_html( printf( esc_html__( 'Inactive (%s)', 'erp' ), esc_html( $count_inactive ) ) ); ?></a></li>
	</ul>

	<form method="post">
	<table class="widefat fixed plugins" cellspacing="0">
		<thead>
			<tr>
				<td scope="col" id="cb" class="manage-column column-cb check-column">&nbsp;</td>
				<th scope="col" id="name" class="manage-column column-name" style="width: 190px;"><?php esc_html_e( 'Title', 'erp' ); ?></th>
				<th scope="col" id="description" class="manage-column column-description"><?php esc_html_e( 'Description', 'erp' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<td scope="col" class="manage-column column-cb check-column">&nbsp;</td>
				<th scope="col" class="manage-column column-name" style="width: 190px;"><?php esc_html_e( 'Title', 'erp' ); ?></th>
				<th scope="col" class="manage-column column-description"><?php esc_html_e( 'Description', 'erp' ); ?></th>
			</tr>
		</tfoot>

		<tbody id="the-list">

		<?php

			foreach ( $modules as $slug => $module ) {
				$checked = array_key_exists( $slug, $all_active_modules ) ? $slug : '';
				?>
				<tr class="active">
					<th scope="row">
						<input type="checkbox" name="modules[]" id="erp_module_<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $slug, $checked ); ?>>
					</th>
					<td class="plugin-title" style="width: 190px;">
						<label for="erp_module_<?php echo esc_attr( $slug ); ?>">
							<strong><?php echo isset( $module['title'] ) ? esc_html( $module['title'] ) : ''; ?></strong>
						</label>
					</td>
					<td class="column-description desc">
						<div class="plugin-description">
							<p><?php echo isset( $module['description'] ) ? esc_html( $module['description'] ) : ''; ?></p>
						</div>
					</td>
				</tr>
				<?php
			}

			if ( ! $modules  ) {
				?>
				<tr class="active">
					<td colspan="3" class="column-description desc">
						<?php esc_html_e( 'No modules found!', 'erp' ); ?>
					</td>
				</tr>
				<?php
			}
		?>

		</tbody>
	</table>
	<p class="submit clear">
		<?php wp_nonce_field( 'erp_nonce', 'erp_settings' ); ?>
		<input class="button-primary" type="submit" name="erp_module_status"  value="Save Settings">
	</p>
	</form>

</div>
