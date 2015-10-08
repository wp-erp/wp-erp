<?php
namespace WeDevs\ERP\Admin\setting;

/**
 * Administration Settings menu
 */
class ERP_Settings {
	
	/**
     * Kick-in the class
     */
	function __construct() {
		$this->settings_scripts();
		
		require_once dirname( __FILE__ ) . '/includes/functions.php';
        require_once dirname( __FILE__ ) . '/includes/class-utilities.php';
        require_once dirname( __FILE__ ) . '/includes/class-settings-page.php';
        require_once dirname( __FILE__ ) . '/includes/class-settings.php';

        include dirname( __FILE__ ) . '/includes/views/settings-page.php';
	}

	/**
     * Get settings scripts
     *
     * @return void
     */
	function settings_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_register_script( 'erp-admin-settings', WPERP_URL . '/includes/admin/setting/assets/js/settings' . $suffix . '.js', array( 'jquery' ), false, true );
        wp_register_style( 'erp-admin-settings', WPERP_URL . '/includes/admin/setting/assets/css/admin' . $suffix . '.css' );
        
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'erp-admin-settings' );

        wp_enqueue_media();
        wp_enqueue_script( 'erp-tiptip' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'erp-admin-settings' );
    }
}

