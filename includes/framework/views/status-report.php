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
	<p><?php _e( 'Please copy and paste this information in your ticket when contacting support:', 'erp' ); ?></p>
	<p class="submit">
		<a href="#" class="button-primary debug-report">
			<?php _e( 'Get system report', 'erp' ); ?>
		</a>
		<a class="button-secondary docs" href="https://wperp.com/docs/erp-core/understanding-the-erp-system-status-report/" target="_blank">
			<?php _e( 'Understanding the status report', 'erp' ); ?>
		</a>
	</p>
	<div id="debug-report">
		<textarea readonly="readonly"></textarea>
		<p class="submit">
			<button id="copy-for-support" class="button-primary" href="#" data-tip="<?php esc_attr_e( 'Copied!', 'erp' ); ?>">
				<?php _e( 'Copy for support', 'erp' ); ?>
			</button>
		</p>
		<p class="copy-error hidden">
			<?php _e( 'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'erp' ); ?>
		</p>
	</div>
</div>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WP ERP"><h2><?php _e( 'WP ERP', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="HR Management">
				<strong><em class="erp-mini-title"># <?php _e( 'HR Management', 'erp' ); ?></em></strong>
			</td>
		</tr>
		<tr>
			<td data-export-label="Employees"><?php _e( 'Employees', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total employees count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\HRM\Models\Employee::count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Departments"><?php _e( 'Departments', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total departments count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\HRM\Models\Department::count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Designations"><?php _e( 'Designations', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total designations count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\HRM\Models\Designation::count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Leave requests"><?php _e( 'Leave requests', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total leave requests count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\HRM\Models\Leave_request::count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Leave policies"><?php _e( 'Leave policies', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total leave policies count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\HRM\Models\Leave_Policies::count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="CRM">
				<strong><em class="erp-mini-title"># <?php _e( 'CRM', 'erp' ); ?></em></strong>
			</td>
		</tr>
		<tr>
			<td data-export-label="Contacts"><?php _e( 'Contacts', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total contacts count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Framework\Models\People::type( 'contact' )->count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Companies"><?php _e( 'Companies', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total companies count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Framework\Models\People::type( 'company' )->count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Contact Groups"><?php _e( 'Contact Groups', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total contact groups count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\CRM\Models\ContactGroup::count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Accounting">
				<strong><em class="erp-mini-title"># <?php _e( 'Accounting', 'erp' ); ?></em></strong>
			</td>
		</tr>
		<tr>
			<td data-export-label="Customers"><?php _e( 'Customers', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total customers count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Framework\Models\People::type( 'customer' )->count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Vendors"><?php _e( 'Vendors', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total vendors count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Framework\Models\People::type( 'vendor' )->count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Sales Transactions"><?php _e( 'Sales Transactions', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total sales transactions count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Accounting\Model\Transaction::type( 'sales' )->count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Expenses Transactions"><?php _e( 'Expenses Transactions', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total expenses transactions count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Accounting\Model\Transaction::type( 'expense' )->count(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Journal Entries"><?php _e( 'Journal Entries', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Total journal transactions count.', 'erp' ) ); ?></td>
			<td><?php echo \WeDevs\ERP\Accounting\Model\Transaction::type( 'journal' )->count(); ?></td>
		</tr>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Settings"><h2><?php _e( 'ERP Settings', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Active Modules"><?php _e( 'Active Modules', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Currently active modules.', 'erp' ) ); ?></td>
			<td><?php
			$modules = get_option( 'erp_modules', [] );

			foreach ( $modules as $module ) {
				echo '<span>'. $module['title'] .'</span> &nbsp; &nbsp; &nbsp;';
			}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Company Start Date"><?php _e( 'Company Start Date', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The date the company officially started.', 'erp' ) ); ?></td>
			<td><?php echo erp_get_option( 'gen_com_start', 'erp_settings_general' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Financial Year Start Date"><?php _e( 'Financial Year Start Date', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Financial and tax calculation starts from this month of every year.', 'erp' ) ); ?></td>
			<td><?php echo erp_financial_start_date(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Financial Year End Date"><?php _e( 'Financial Year End Date', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The date the company\'s financial year ends.', 'erp' ) ); ?></td>
			<td><?php echo erp_financial_end_date(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Date Format"><?php _e( 'Date Format', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Format of date to show accross the system.', 'erp' ) ); ?></td>
			<td><?php echo erp_get_option( 'date_format', 'erp_settings_general' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Enable SMTP"><?php _e( 'Enable SMTP', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_get_option( 'enable_smtp' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Enable IMAP"><?php _e( 'Enable IMAP', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_get_option( 'enable_imap' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Currency"><?php _e( 'Currency', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_get_currency(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Currency Position"><?php _e( 'Currency Position', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_get_option( 'erp_ac_currency_position', false, 'left' ); ?></td>
		</tr>

		<?php if ( erp_is_module_active( 'accounting' ) ) : ?>
		<tr>
			<td data-export-label="Thousand Separator"><?php _e( 'Thousand Separator', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_ac_get_price_thousand_separator(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Decimal Separator"><?php _e( 'Decimal Separator', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_ac_get_price_decimal_separator(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Number of Decimals"><?php _e( 'Number of Decimals', 'erp' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo erp_ac_get_price_decimals(); ?></td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WordPress Environment"><h2><?php _e( 'WordPress environment', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Home URL"><?php _e( 'Home URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The homepage URL of your site.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $environment['home_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Site URL"><?php _e( 'Site URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The root URL of your site.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $environment['site_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="ERP Version"><?php _e( 'ERP version', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The version of erp installed on your site.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $environment['version'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Log Directory Writable"><?php _e( 'Log directory writable', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Several erp extensions can write logs which makes debugging problems easier. The directory must be writable for this to happen.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['log_directory_writable'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> <code class="private">' . esc_html( $environment['log_directory'] ) . '</code></mark> ';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'To allow logging, make %1$s writable or define a custom %2$s.', 'erp' ), '<code>' . $environment['log_directory'] . '</code>', '<code>ERP_LOG_DIR</code>' ) . '</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Version"><?php _e( 'WP version', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The version of WordPress installed on your site.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $environment['wp_version'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Multisite"><?php _e( 'WP multisite', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Whether or not you have WordPress Multisite enabled.', 'erp' ) ); ?></td>
			<td><?php echo ( $environment['wp_multisite'] ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Memory Limit"><?php _e( 'WP memory limit', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The maximum amount of memory (RAM) that your site can use at one time.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['wp_memory_limit'] < 67108864 ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'erp' ), size_format( $environment['wp_memory_limit'] ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'erp' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . size_format( $environment['wp_memory_limit'] ) . '</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Debug Mode"><?php _e( 'WP debug mode', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Displays whether or not WordPress is in Debug Mode.', 'erp' ) ); ?></td>
			<td>
				<?php if ( $environment['wp_debug_mode'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="no">&ndash;</mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="WP Cron"><?php _e( 'WP cron', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Displays whether or not WP Cron Jobs are enabled.', 'erp' ) ); ?></td>
			<td>
				<?php if ( $environment['wp_cron'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="no">&ndash;</mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Language"><?php _e( 'Language', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The current language used by WordPress. Default = English', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $environment['language'] ) ?></td>
		</tr>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Server Environment"><h2><?php _e( 'Server environment', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Server Info"><?php _e( 'Server info', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Information about the web server that is currently hosting your site.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $environment['server_info'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Version"><?php _e( 'PHP version', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The version of PHP installed on your hosting server.', 'erp' ) ); ?></td>
			<td><?php
				if ( version_compare( $environment['php_version'], '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum PHP version of 5.6. See: %2$s', 'erp' ), esc_html( $environment['php_version'] ), '<a href="https://docs.erp.com/document/how-to-update-your-php-version/" target="_blank">' . __( 'How to update your PHP version', 'erp' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $environment['php_version'] ) . '</mark>';
				}
				?></td>
		</tr>
		<?php if ( function_exists( 'ini_get' ) ) : ?>
			<tr>
				<td data-export-label="PHP Post Max Size"><?php _e( 'PHP post max size', 'erp' ); ?>:</td>
				<td class="help"><?php echo erp_help_tip( __( 'The largest filesize that can be contained in one post.', 'erp' ) ); ?></td>
				<td><?php echo esc_html( size_format( $environment['php_post_max_size'] ) ) ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Time Limit"><?php _e( 'PHP time limit', 'erp' ); ?>:</td>
				<td class="help"><?php echo erp_help_tip( __( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'erp' ) ); ?></td>
				<td><?php echo esc_html( $environment['php_max_execution_time'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Max Input Vars"><?php _e( 'PHP max input vars', 'erp' ); ?>:</td>
				<td class="help"><?php echo erp_help_tip( __( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'erp' ) ); ?></td>
				<td><?php echo esc_html( $environment['php_max_input_vars'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="cURL Version"><?php _e( 'cURL version', 'erp' ); ?>:</td>
				<td class="help"><?php echo erp_help_tip( __( 'The version of cURL installed on your server.', 'erp' ) ); ?></td>
				<td><?php echo esc_html( $environment['curl_version'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN installed', 'erp' ); ?>:</td>
				<td class="help"><?php echo erp_help_tip( __( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'erp' ) ); ?></td>
				<td><?php echo $environment['suhosin_installed'] ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
			</tr>
		<?php endif;
		if ( $wpdb->use_mysqli ) {
			$ver = mysqli_get_server_info( $wpdb->dbh );
		} else {
			$ver = mysql_get_server_info();
		}
		if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) : ?>
			<tr>
				<td data-export-label="MySQL Version"><?php _e( 'MySQL version', 'erp' ); ?>:</td>
				<td class="help"><?php echo erp_help_tip( __( 'The version of MySQL installed on your hosting server.', 'erp' ) ); ?></td>
				<td>
					<?php
					if ( version_compare( $environment['mysql_version'], '5.6', '<' ) ) {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'erp' ), esc_html( $environment['mysql_version'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress requirements', 'erp' ) . '</a>' ) . '</mark>';
					} else {
						echo '<mark class="yes">' . esc_html( $environment['mysql_version'] ) . '</mark>';
					}
					?>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td data-export-label="Max Upload Size"><?php _e( 'Max upload size', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The largest filesize that can be uploaded to your WordPress installation.', 'erp' ) ); ?></td>
			<td><?php echo size_format( $environment['max_upload_size'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Default Timezone is UTC"><?php _e( 'Default timezone is UTC', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The default timezone for your server.', 'erp' ) ); ?></td>
			<td><?php
				if ( 'UTC' !== $environment['default_timezone'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'erp' ), $environment['default_timezone'] ) . '</mark>';
				} else {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="fsockopen/cURL"><?php _e( 'fsockopen/cURL', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['fsockopen_or_curl_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'erp' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="SoapClient"><?php _e( 'SoapClient', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['soapclient_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'erp' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="DOMDocument"><?php _e( 'DOMDocument', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'HTML/Multipart emails use DOMDocument to generate inline CSS in templates.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['domdocument_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'erp' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="GZip"><?php _e( 'GZip', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'GZip (gzopen) is used to open the GEOIP database from MaxMind.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['gzip_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'erp' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Multibyte String"><?php _e( 'Multibyte string', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Multibyte String (mbstring) is used to convert character encoding, like for emails or converting characters to lowercase.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['mbstring_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'erp' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Remote Post"><?php _e( 'Remote post', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'PayPal uses this method of communicating when sending back transaction information.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['remote_post_successful'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s failed. Contact your hosting provider.', 'erp' ), 'wp_remote_post()' ) . ' ' . esc_html( $environment['remote_post_response'] ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Remote Get"><?php _e( 'Remote get', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'ERP plugins may use this method of communication when checking for plugin updates.', 'erp' ) ); ?></td>
			<td><?php
				if ( $environment['remote_get_successful'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s failed. Contact your hosting provider.', 'erp' ), 'wp_remote_get()' ) . ' ' . esc_html( $environment['remote_get_response'] ) . '</mark>';
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
				<td class="help"><?php echo isset( $row['help'] ) ? $row['help'] : ''; ?></td>
				<td>
					<mark class="<?php echo esc_attr( $css_class ); ?>">
						<?php echo $icon; ?>  <?php echo ! empty( $row['note'] ) ? wp_kses_data( $row['note'] ) : ''; ?>
					</mark>
				</td>
			</tr><?php
		} ?>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Database"><h2><?php _e( 'Database', 'erp' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="ERP Database Version"><?php _e( 'ERP database version', 'erp' ); ?>:</td>
        <td class="help"><?php echo erp_help_tip( __( 'The version of erp that the database is formatted for. This should be the same as your erp version.', 'erp' ) ); ?></td>
        <td><?php echo esc_html( $database['wp_erp_db_version'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="ERP Database Prefix"><?php _e( 'Database prefix', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php
			if ( strlen( $database['database_prefix'] ) > 20 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend using a prefix with less than 20 characters. See: %2$s', 'erp' ), esc_html( $database['database_prefix'] ), '<a href="https://docs.erp.com/help" target="_blank">' . __( 'How to update your database table prefix', 'erp' ) . '</a>' ) . '</mark>';
			} else {
				echo '<mark class="yes">' . esc_html( $database['database_prefix'] ) . '</mark>';
			}
			?>
        </td>
    </tr>

    <tr>
        <td><?php _e( 'Total Database Size', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', $database['database_size']['data'] + $database['database_size']['index'] ); ?></td>
    </tr>

    <tr>
        <td><?php _e( 'Database Data Size', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', $database['database_size']['data'] ); ?></td>
    </tr>

    <tr>
        <td><?php _e( 'Database Index Size', 'erp' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', $database['database_size']['index'] ); ?></td>
    </tr>

    </tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Post Type Counts"><h2><?php _e( 'Post Type Counts', 'erp' ); ?></h2></th>
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
			<th colspan="3" data-export-label="Security"><h2><?php _e( 'Security', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Secure connection (HTTPS)"><?php _e( 'Secure connection (HTTPS)', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Is the connection to your website secure?', 'erp' ) ); ?></td>
			<td>
				<?php if ( $security['secure_connection'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="error"><span class="dashicons dashicons-warning"></span><?php printf( __( 'Your website is not using HTTPS. <a href="%s" target="_blank">Learn more about HTTPS and SSL Certificates</a>.', 'erp' ), 'https://docs.erp.com/document/ssl-and-https/' ); ?></mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Hide errors from visitors"><?php _e( 'Hide errors from visitors', 'erp' ); ?></td>
			<td class="help"><?php echo erp_help_tip( __( 'Error messages can contain sensitive information about your store environment. These should be hidden from untrusted visitors.', 'erp' ) ); ?></td>
			<td>
				<?php if ( $security['hide_errors'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="error"><span class="dashicons dashicons-warning"></span><?php _e( 'Error messages should not be shown to visitors.', 'erp' ); ?></mark>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>

<table class="erp_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Active Plugins (<?php echo count( $active_plugins ) ?>)"><h2><?php _e( 'Active plugins', 'erp' ); ?> (<?php echo count( $active_plugins ) ?>)</h2></th>
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
					$plugin_name = '<a href="' . esc_url( $plugin['url'] ) . '" aria-label="' . esc_attr__( 'Visit plugin homepage' , 'erp' ) . '" target="_blank">' . $plugin_name . '</a>';
				}
				?>
				<tr>
					<td><?php echo $plugin_name; ?></td>
					<td class="help">&nbsp;</td>
					<td><?php
						/* translators: %s: plugin author */
						printf( __( 'by %s', 'erp' ), $plugin['author_name'] );
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
			<th colspan="3" data-export-label="Theme"><h2><?php _e( 'Theme', 'erp' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Name"><?php _e( 'Name', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The name of the current active theme.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $theme['name'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Version"><?php _e( 'Version', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The installed version of the current active theme.', 'erp' ) ); ?></td>
			<td><?php
				echo esc_html( $theme['version'] );
				if ( version_compare( $theme['version'], $theme['version_latest'], '<' ) ) {
					/* translators: %s: theme latest version */
					echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'erp' ), esc_html( $theme['version_latest'] ) ) . '</strong>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Author URL"><?php _e( 'Author URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The theme developers URL.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $theme['author_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Child Theme"><?php _e( 'Child theme', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'Displays whether or not the current theme is a child theme.', 'erp' ) ); ?></td>
			<td><?php
				echo $theme['is_child_theme'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . sprintf( __( 'If you are modifying erp on a parent theme that you did not build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'erp' ), 'https://codex.wordpress.org/Child_Themes' );
			?></td>
		</tr>
		<?php
		if ( $theme['is_child_theme'] ) :
		?>
		<tr>
			<td data-export-label="Parent Theme Name"><?php _e( 'Parent theme name', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The name of the parent theme.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $theme['parent_name'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Version"><?php _e( 'Parent theme version', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The installed version of the parent theme.', 'erp' ) ); ?></td>
			<td><?php
				echo esc_html( $theme['parent_version'] );
				if ( version_compare( $theme['parent_version'], $theme['parent_version_latest'], '<' ) ) {
					/* translators: %s: parant theme latest version */
					echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'erp' ), esc_html( $theme['parent_version_latest'] ) ) . '</strong>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Author URL"><?php _e( 'Parent theme author URL', 'erp' ); ?>:</td>
			<td class="help"><?php echo erp_help_tip( __( 'The parent theme developers URL.', 'erp' ) ); ?></td>
			<td><?php echo esc_html( $theme['parent_author_url'] ) ?></td>
		</tr>
		<?php endif ?>
	</tbody>
</table>

<?php do_action( 'erp_system_status_report' ); ?>
