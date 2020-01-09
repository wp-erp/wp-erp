<?php
/**
 * Admin View: Page - Status Report.
 */

global $wpdb;

$system_status    = new \WeDevs\ERP\Framework\System_Status;
$environment      = $system_status->get_environment_info();
$database         = $system_status->get_database_info();
$post_type_counts = $system_status->get_post_type_counts();
$active_plugins   = $system_status->get_active_plugins();
$theme            = $system_status->get_theme_info();
$security         = $system_status->get_security_info();

?>
<div class="updated erp-message inline">
	<p><?php esc_html_e( 'Please copy and paste this information in your ticket when contacting support:', 'erp' ); ?></p>
	<p class="submit">
		<a href="#" class="button-primary debug-report">
			<?php esc_html_e( 'Get system report', 'erp' ); ?>
		</a>
		<a class="button-secondary docs" href="https://wperp.com/docs/erp-core/understanding-the-erp-system-status-report/" target="_blank">
			<?php esc_html_e( 'Understanding the status report', 'erp' ); ?>
		</a>
	</p>
	<div id="debug-report">
		<textarea readonly="readonly"></textarea>
		<p class="submit">
			<button id="copy-for-support" class="button-primary" href="#" data-tip="<?php esc_attr_e( 'Copied!', 'erp' ); ?>">
				<?php esc_html_e( 'Copy for support', 'erp' ); ?>
			</button>
		</p>
		<p class="copy-error hidden">
			<?php esc_html_e( 'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'erp' ); ?>
		</p>
	</div>
</div>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WP ERP"><h2><?php esc_html_e( 'WP ERP', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="HR Management">
				<strong><em class="erp-mini-title"># <?php esc_html_e( 'HR Management', 'erp' ); ?></em></strong>
			</td>
		</tr>
		<tr>
			<td data-export-label="Employees"><?php esc_html_e( 'Employees', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total employees count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\HRM\Models\Employee::count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Departments"><?php esc_html_e( 'Departments', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total departments count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\HRM\Models\Department::count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Designations"><?php esc_html_e( 'Designations', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total designations count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\HRM\Models\Designation::count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Leave requests"><?php esc_html_e( 'Leave requests', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total leave requests count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\HRM\Models\Leave_request::count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Leave policies"><?php esc_html_e( 'Leave policies', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total leave policies count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\HRM\Models\Leave_Policies::count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="CRM">
				<strong><em class="erp-mini-title"># <?php esc_html_e( 'CRM', 'erp' ); ?></em></strong>
			</td>
		</tr>
		<tr>
			<td data-export-label="Contacts"><?php esc_html( esc_html_e( 'Contacts', 'erp' ) ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total contacts count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\Framework\Models\People::type( 'contact' )->count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Companies"><?php esc_html_e( 'Companies', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total companies count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\Framework\Models\People::type( 'company' )->count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Contact Groups"><?php esc_html_e( 'Contact Groups', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total contact groups count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\CRM\Models\ContactGroup::count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Accounting">
				<strong><em class="erp-mini-title"># <?php esc_html_e( 'Accounting', 'erp' ); ?></em></strong>
			</td>
		</tr>
		<tr>
			<td data-export-label="Customers"><?php esc_html_e( 'Customers', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total customers count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\Framework\Models\People::type( 'customer' )->count() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Vendors"><?php esc_html_e( 'Vendors', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Total vendors count.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( \WeDevs\ERP\Framework\Models\People::type( 'vendor' )->count() ); ?></td>
		</tr>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Settings"><h2><?php esc_html_e( 'ERP Settings', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Active Modules"><?php esc_html_e( 'Active Modules', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Currently active modules.', 'erp' ) ) ); ?></td>
			<td><?php
			$modules = get_option( 'erp_modules', [] );

			foreach ( $modules as $module ) {
				echo '<span>'. esc_html( $module['title'] ) .'</span> &nbsp; &nbsp; &nbsp;';
			}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Company Start Date"><?php esc_html_e( 'Company Start Date', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The date the company officially started.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( erp_get_option( 'gen_com_start', 'erp_settings_general' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Financial Year Start Date"><?php esc_html_e( 'Financial Year Start Date', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Financial and tax calculation starts from this month of every year.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( erp_financial_start_date() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Financial Year End Date"><?php esc_html_e( 'Financial Year End Date', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The date the company\'s financial year ends.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( erp_financial_end_date() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Date Format"><?php esc_html_e( 'Date Format', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Format of date to show accross the system.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( erp_get_option( 'date_format', 'erp_settings_general' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Enable SMTP"><?php esc_html_e( 'Enable SMTP', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( erp_get_option( 'enable_smtp' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Enable IMAP"><?php esc_html_e( 'Enable IMAP', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( erp_get_option( 'enable_imap' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Currency"><?php esc_html_e( 'Currency', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( erp_get_currency() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Currency Position"><?php esc_html_e( 'Currency Position', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( erp_get_option( 'erp_ac_currency_position', false, 'left' ) ); ?></td>
		</tr>

		<?php if ( erp_is_module_active( 'accounting' ) ) : ?>
		<tr>
			<td data-export-label="Thousand Separator"><?php esc_html_e( 'Thousand Separator', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( erp_ac_get_price_thousand_separator() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Decimal Separator"><?php esc_html_e( 'Decimal Separator', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( erp_get_option( 'erp_ac_de_separator', false, '.' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Number of Decimals"><?php esc_html_e( 'Number of Decimals', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( absint( erp_get_option( 'erp_ac_nm_decimal', false, 2 ) ) ); ?></td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WordPress Environment"><h2><?php esc_html_e( 'WordPress environment', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Home URL"><?php esc_html_e( 'Home URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The homepage URL of your site.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $environment['home_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Site URL"><?php esc_html_e( 'Site URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The root URL of your site.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $environment['site_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="ERP Version"><?php esc_html_e( 'ERP version', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The version of erp installed on your site.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $environment['version'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Log Directory Writable"><?php esc_html_e( 'Log directory writable', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Several erp extensions can write logs which makes debugging problems easier. The directory must be writable for this to happen.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['log_directory_writable'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> <code class="private">' . esc_url( $environment['log_directory'] ) . '</code></mark> ';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . wp_kses_post( sprintf( esc_html__( 'To allow logging, make %1$s writable or define a custom %2$s.', 'erp' ), '<code>' . $environment['log_directory'] . '</code>', '<code>ERP_LOG_DIR</code>' ) ) . '</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Version"><?php esc_html_e( 'WP version', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The version of WordPress installed on your site.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $environment['wp_version'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Multisite"><?php esc_html_e( 'WP multisite', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Whether or not you have WordPress Multisite enabled.', 'erp' ) ) ); ?></td>
			<td><?php echo ( $environment['wp_multisite'] ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Memory Limit"><?php esc_html_e( 'WP memory limit', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The maximum amount of memory (RAM) that your site can use at one time.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['wp_memory_limit'] < 67108864 ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html( sprintf( esc_html__( '%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'erp' ), size_format( $environment['wp_memory_limit'] ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . esc_html__( 'Increasing memory allocated to PHP', 'erp' ) . '</a>' ) ) . '</mark>';
				} else {
					echo '<mark class="yes">' .  esc_html( size_format( $environment['wp_memory_limit'] ) ) . '</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Debug Mode"><?php esc_html_e( 'WP debug mode', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Displays whether or not WordPress is in Debug Mode.', 'erp' ) ) ); ?></td>
			<td>
				<?php if ( $environment['wp_debug_mode'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="no">&ndash;</mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="WP Cron"><?php esc_html_e( 'WP cron', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Displays whether or not WP Cron Jobs are enabled.', 'erp' ) ) ); ?></td>
			<td>
				<?php if ( $environment['wp_cron'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="no">&ndash;</mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Language"><?php esc_html_e( 'Language', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The current language used by WordPress. Default = English', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $environment['language'] ) ?></td>
		</tr>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Server Environment"><h2><?php esc_html_e( 'Server environment', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Server Info"><?php esc_html_e( 'Server info', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Information about the web server that is currently hosting your site.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $environment['server_info'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Version"><?php esc_html_e( 'PHP version', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The version of PHP installed on your hosting server.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( version_compare( $environment['php_version'], '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend a minimum PHP version of 5.6. See: %2$s', 'erp' ), esc_html( $environment['php_version'] ), '<a href="https://docs.erp.com/document/how-to-update-your-php-version/" target="_blank">' . esc_html__( 'How to update your PHP version', 'erp' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $environment['php_version'] ) . '</mark>';
				}
				?></td>
		</tr>
		<?php if ( function_exists( 'ini_get' ) ) : ?>
			<tr>
				<td data-export-label="PHP Post Max Size"><?php esc_html_e( 'PHP post max size', 'erp' ); ?>:</td>
				<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The largest filesize that can be contained in one post.', 'erp' ) ) ); ?></td>
				<td><?php echo esc_html( size_format( $environment['php_post_max_size'] ) ) ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Time Limit"><?php esc_html_e( 'PHP time limit', 'erp' ); ?>:</td>
				<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'erp' ) ) ); ?></td>
				<td><?php echo esc_html( $environment['php_max_execution_time'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Max Input Vars"><?php esc_html_e( 'PHP max input vars', 'erp' ); ?>:</td>
				<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'erp' ) ) ); ?></td>
				<td><?php echo esc_html( $environment['php_max_input_vars'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="cURL Version"><?php esc_html_e( 'cURL version', 'erp' ); ?>:</td>
				<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The version of cURL installed on your server.', 'erp' ) ) ); ?></td>
				<td><?php echo esc_html( $environment['curl_version'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="SUHOSIN Installed"><?php esc_html_e( 'SUHOSIN installed', 'erp' ); ?>:</td>
				<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'erp' ) ) ); ?></td>
				<td><?php echo esc_html( $environment['suhosin_installed'] ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
			</tr>
		<?php endif;
		if ( $wpdb->use_mysqli ) {
			$ver = mysqli_get_server_info( $wpdb->dbh );
		} else {
			$ver = mysql_get_server_info();
		}
		if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) : ?>
			<tr>
				<td data-export-label="MySQL Version"><?php esc_html_e( 'MySQL version', 'erp' ); ?>:</td>
				<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The version of MySQL installed on your hosting server.', 'erp' ) ) ); ?></td>
				<td>
					<?php
					if ( version_compare( $environment['mysql_version'], '5.6', '<' ) ) {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'erp' ), esc_html( $environment['mysql_version'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__( 'WordPress requirements', 'erp' ) . '</a>' ) . '</mark>';
					} else {
						echo '<mark class="yes">' . esc_html( $environment['mysql_version'] ) . '</mark>';
					}
					?>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td data-export-label="Max Upload Size"><?php esc_html_e( 'Max upload size', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The largest filesize that can be uploaded to your WordPress installation.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( size_format( $environment['max_upload_size'] ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Default Timezone is UTC"><?php esc_html_e( 'Default timezone is UTC', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The default timezone for your server.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( 'UTC' !== $environment['default_timezone'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html( sprintf( esc_html__( 'Default timezone is %s - it should be UTC', 'erp' ), $environment['default_timezone'] ) ) . '</mark>';
				} else {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="fsockopen/cURL"><?php esc_html_e( 'fsockopen/cURL', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['fsockopen_or_curl_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'erp' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="SoapClient"><?php esc_html_e( 'SoapClient', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['soapclient_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'erp' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="DOMDocument"><?php esc_html_e( 'DOMDocument', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'HTML/Multipart emails use DOMDocument to generate inline CSS in templates.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['domdocument_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'erp' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="GZip"><?php esc_html_e( 'GZip', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'GZip (gzopen) is used to open the GEOIP database from MaxMind.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['gzip_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'erp' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Multibyte String"><?php esc_html_e( 'Multibyte string', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Multibyte String (mbstring) is used to convert character encoding, like for emails or converting characters to lowercase.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['mbstring_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'erp' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Remote Post"><?php esc_html_e( 'Remote post', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'PayPal uses this method of communicating when sending back transaction information.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['remote_post_successful'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%s failed. Contact your hosting provider.', 'erp' ), 'wp_remote_post()' ) . ' ' . esc_html( $environment['remote_post_response'] ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Remote Get"><?php esc_html_e( 'Remote get', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'ERP plugins may use this method of communication when checking for plugin updates.', 'erp' ) ) ); ?></td>
			<td><?php
				if ( $environment['remote_get_successful'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%s failed. Contact your hosting provider.', 'erp' ), 'wp_remote_get()' ) . ' ' . esc_html( $environment['remote_get_response'] ) . '</mark>';
				} ?>
			</td>
		</tr>
		<?php
		$rows = apply_filters( 'erp_system_status_environment_rows', array() );
		foreach ( $rows as $row ) {
			if ( ! empty( $row['success'] ) ) {
				$css_class = 'yes';
				$icon = '<span class="dashicons dashicons-yes"></span>';
			} else {
				$css_class = 'error';
				$icon = '<span class="dashicons dashicons-no-alt"></span>';
			}
			?>
			<tr>
				<td data-export-label="<?php echo esc_attr( $row['name'] ); ?>"><?php echo esc_html( $row['name'] ); ?>:</td>
				<td class="help"><?php echo isset( $row['help'] ) ? esc_html( $row['help'] ) : ''; ?></td>
				<td>
					<mark class="<?php echo esc_attr( $css_class ); ?>">
						<?php echo wp_kses_data( $icon ); ?>  <?php echo ! empty( $row['note'] ) ? wp_kses_data( $row['note'] ) : ''; ?>
					</mark>
				</td>
			</tr><?php
		} ?>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Database"><h2><?php esc_html_e( 'Database', 'erp' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="ERP Database Version"><?php esc_html_e( 'ERP database version', 'erp' ); ?>:</td>
        <td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The version of erp that the database is formatted for. This should be the same as your erp version.', 'erp' ) ) ); ?></td>
        <td><?php echo esc_html( $database['wp_erp_db_version'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="ERP Database Prefix"><?php esc_html_e( 'Database prefix', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php
			if ( strlen( $database['database_prefix'] ) > 20 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend using a prefix with less than 20 characters. See: %2$s', 'erp' ), esc_html( $database['database_prefix'] ), '<a href="https://docs.erp.com/help" target="_blank">' . esc_html__( 'How to update your database table prefix', 'erp' ) . '</a>' ) . '</mark>';
			} else {
				echo '<mark class="yes">' . esc_html( $database['database_prefix'] ) . '</mark>';
			}
			?>
        </td>
    </tr>

    <tr>
        <td><?php esc_html_e( 'Total Database Size', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', esc_html( $database['database_size']['data'] ) + esc_html( $database['database_size']['index'] ) ); ?></td>
    </tr>

    <tr>
        <td><?php esc_html_e( 'Database Data Size', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', esc_html( $database['database_size']['data'] ) ); ?></td>
    </tr>

    <tr>
        <td><?php esc_html_e( 'Database Index Size', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', esc_html( $database['database_size']['index'] ) ); ?></td>
    </tr>

    </tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Post Type Counts"><h2><?php esc_html_e( 'Post Type Counts', 'erp' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ( $post_type_counts as $post_type ) {
		?>
        <tr>
            <td><?php echo esc_html( $post_type->type ); ?></td>
            <td class="help">&nbsp;</td>
            <td><?php echo absint( $post_type->count ); ?></td>
        </tr>
		<?php
	}
	?>
    </tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Security"><h2><?php esc_html_e( 'Security', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Secure connection (HTTPS)"><?php esc_html_e( 'Secure connection (HTTPS)', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Is the connection to your website secure?', 'erp' ) ) ); ?></td>
			<td>
				<?php if ( $security['secure_connection'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="error"><span class="dashicons dashicons-warning"></span><?php wp_kses_post( printf( __( 'Your website is not using HTTPS. <a href="%s" target="_blank">Learn more about HTTPS and SSL Certificates</a>.', 'erp' ), 'https://docs.erp.com/document/ssl-and-https/' ) ); ?></mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Hide errors from visitors"><?php esc_html_e( 'Hide errors from visitors', 'erp' ); ?></td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'Error messages can contain sensitive information about your store environment. These should be hidden from untrusted visitors.', 'erp' ) ) ); ?></td>
			<td>
				<?php if ( $security['hide_errors'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="error"><span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Error messages should not be shown to visitors.', 'erp' ); ?></mark>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Active Plugins (<?php echo count( $active_plugins ) ?>)"><h2><?php esc_html_e( 'Active plugins', 'erp' ); ?> (<?php echo count( $active_plugins ) ?>)</h2></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $active_plugins as $plugin ) {
			if ( ! empty( $plugin['name'] ) ) {
				$dirname = dirname( $plugin['plugin'] );

				// Link the plugin name to the plugin url if available.
				$plugin_name = esc_html( $plugin['name'] );
				if ( ! empty( $plugin['url'] ) ) {
					$plugin_name = '<a href="' . esc_url( $plugin['url'] ) . '" aria-label="' . esc_html__( 'Visit plugin homepage' , 'erp' ) . '" target="_blank">' . $plugin_name . '</a>';
				}
				?>
				<tr>
					<td><?php echo wp_kses_post( $plugin_name ); ?></td>
					<td class="help">&nbsp;</td>
					<td><?php
						/* translators: %s: plugin author */
						printf( esc_html__( 'by %s', 'erp' ), esc_html( $plugin['author_name'] ) );
						echo ' &ndash; ' . esc_html( $plugin['version'] );
					?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Theme"><h2><?php esc_html_e( 'Theme', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Name"><?php esc_html_e( 'Name', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The name of the current active theme.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $theme['name'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Version"><?php esc_html_e( 'Version', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( __( 'The installed version of the current active theme.', 'erp' ) ) ); ?></td>
			<td><?php
				echo esc_html( $theme['version'] );
				if ( version_compare( $theme['version'], $theme['version_latest'], '<' ) ) {
					/* translators: %s: theme latest version */
					echo ' &ndash; <strong style="color:red;">' . esc_html( sprintf( __( '%s is available', 'erp' ), esc_html( $theme['version_latest'] ) ) ) . '</strong>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Author URL"><?php esc_html_e( 'Author URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( __( 'The theme developers URL.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $theme['author_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Child Theme"><?php esc_html_e( 'Child theme', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( __( 'Displays whether or not the current theme is a child theme.', 'erp' ) ) ); ?></td>
			<td><?php
				echo $theme['is_child_theme'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . wp_kses_post( sprintf( __( 'If you are modifying erp on a parent theme that you did not build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'erp' ), 'https://codex.wordpress.org/Child_Themes' ) );
			?></td>
		</tr>
		<?php
		if ( $theme['is_child_theme'] ) :
		?>
		<tr>
			<td data-export-label="Parent Theme Name"><?php esc_html_e( 'Parent theme name', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( __( 'The name of the parent theme.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $theme['parent_name'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Version"><?php esc_html_e( 'Parent theme version', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The installed version of the parent theme.', 'erp' ) ) ); ?></td>
			<td><?php
				echo esc_html( $theme['parent_version'] );
				if ( version_compare( $theme['parent_version'], $theme['parent_version_latest'], '<' ) ) {
					/* translators: %s: parant theme latest version */
					echo ' &ndash; <strong style="color:red;">' . esc_html( sprintf( esc_html__( '%s is available', 'erp' ), esc_html( $theme['parent_version_latest'] ) ) ) . '</strong>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Author URL"><?php esc_html_e( 'Parent theme author URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo wp_kses_post( erp_help_tip( esc_html__( 'The parent theme developers URL.', 'erp' ) ) ); ?></td>
			<td><?php echo esc_html( $theme['parent_author_url'] ) ?></td>
		</tr>
		<?php endif ?>
	</tbody>
</table>

<?php do_action( 'erp_system_status_report' ); ?>
