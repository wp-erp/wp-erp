<?php
namespace WeDevs\ERP\HRM;

/**
 * Settings class
 */
class Settings {

    /**
     * [__construct description]
     */
    public function __construct() {
        $actions = array(
            'settings-save-work-days' => 'save_work_days'
        );

        foreach ($actions as $hook => $callback) {
            add_action( 'erp_action_' . $hook, array( $this, $callback )  );
        }
    }

    /**
     * Initializes the WeDevs_ERP() class
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_tabs() {
        $tabs = apply_filters( 'erp_hr_settings_tabs', array(
            'workdays' => array(
                'title'    => __( 'Work Days', 'wp-erp' ),
                'callback' => array( $this, 'tab_work_days' )
            ),
            'termination' => array(
                'title'    => __( 'Termination Reasons', 'wp-erp' ),
                'callback' => ''
            ),
        ) );

        return $tabs;
    }

    /**
     * Render the workdays tab
     *
     * @return void
     */
    public function tab_work_days() {
        include WPERP_HRM_VIEWS . '/settings/tab-workdays.php';
    }

    /**
     * Save work days from settings
     *
     * @return void
     */
    public function save_work_days() {

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-settings' ) ) {
            die();
        }

        $option_key = 'erp_hr_work_days';
        $days       = array_map( 'absint', $_POST['day'] );

        if ( count( $days ) == 7 ) {
            update_option( $option_key, $days );
        }
    }
}

Settings::init();