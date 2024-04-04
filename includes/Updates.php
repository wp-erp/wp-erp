<?php

namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Updates\BP\ERPACCTBGProcess1_5_0;
use WeDevs\ERP\Updates\BP\ERPACCTBGProcessPeopleTrn_1_5_2;

/*
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Installer Class
 */
class Updates {
    use Hooker;

    /** @var array DB updates that need to be run */
    private static $updates = [
        '1.0'     => 'updates/update-1.0.php',
        '1.1.0'   => 'updates/update-1.1.0.php',
        '1.1.1'   => 'updates/update-1.1.1.php',
        '1.1.2'   => 'updates/update-1.1.2.php',
        '1.1.3'   => 'updates/update-1.1.3.php',
        '1.1.5'   => 'updates/update-1.1.5.php',
        '1.1.6'   => 'updates/update-1.1.6.php',
        '1.1.7'   => 'updates/update-1.1.7.php',
        '1.1.8'   => 'updates/update-1.1.8.php',
        '1.1.9'   => 'updates/update-1.1.9.php',
        '1.1.17'  => 'updates/update-1.1.17.php',
        '1.2.1'   => 'updates/update-1.2.1.php',
        '1.2.2'   => 'updates/update-1.2.2.php',
        '1.2.5'   => 'updates/update-1.2.5.php',
        '1.2.7'   => 'updates/update-1.2.7.php',
        '1.3.2'   => 'updates/update-1.3.2.php',
        '1.3.3'   => 'updates/update-1.3.3.php',
        '1.3.4'   => 'updates/update-1.3.4.php',
        '1.5.0'   => 'updates/update-1.5.0.php',
        '1.5.2'   => 'updates/update-1.5.2.php',
        '1.5.4'   => 'updates/update-1.5.4.php',
        '1.5.5'   => 'updates/update-1.5.5.php',
        '1.5.6'   => 'updates/update-1.5.6.php',
        '1.5.16'  => 'updates/update-1.5.16.php',
        '1.6.0'   => 'updates/update-1.6.0.php',
        '1.6.3'   => 'updates/update-1.6.3.php',
        '1.6.5'   => 'updates/update-1.6.5.php',
        '1.6.8'   => 'updates/update-1.6.8.php',
        '1.8.0'   => 'updates/update-1.8.0.php',
        '1.8.1'   => 'updates/update-1.8.1.php',
        '1.8.3'   => 'updates/update-1.8.3.php',
        '1.8.5'   => 'updates/update-1.8.5.php',
        '1.10.0'  => 'updates/update-1.10.0.php',
        '1.10.2'  => 'updates/update-1.10.2.php',
        '1.11.0'  => 'updates/update-1.11.0.php',
        '1.12.6'  => 'updates/update-1.12.6.php',
        '1.12.7'  => 'updates/update-1.12.7.php',
    ];

    /**
     * Current active erp modules
     *
     * @since 1.1.9
     *
     * @var array
     */
    private $active_modules = [];

    /**
     * Binding all events
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'admin_notices', 'show_update_notice' );
        $this->action( 'admin_init', 'do_updates' );

        $this->action( 'erp_update_1_5_0_process_memory_exceeded', 'memory_exceeded' );
        $this->action( 'erp_update_1_5_2_process_memory_exceeded', 'memory_exceeded' );
        $this->action( 'erp_hr_bg_process_1_10_0_memory_exceeded', 'memory_exceeded' );
    }

    /**
     * Check if need any update
     *
     * @since 1.0
     *
     * @return bool
     */
    public function is_needs_update() {
        $installed_version = get_option( 'wp_erp_version' );

        // may be it's the first install
        if ( ! $installed_version ) {
            return false;
        }

        if ( version_compare( $installed_version, WPERP_VERSION, '<' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Show update notice
     *
     * @since 1.0
     *
     * @return void
     */
    public function show_update_notice() {
        if ( ! current_user_can( 'update_plugins' ) || ! $this->is_needs_update() ) {
            return;
        }

        $installed_version  = get_option( 'wp_erp_version' );
        $updatable_versions = array_keys( self::$updates );

        if ( ! is_null( $installed_version ) && version_compare( $installed_version, end( $updatable_versions ), '<' ) ) {
            ?>
                <div id="message" class="updated">
                    <p><?php echo wp_kses_post( __( '<strong>WP ERP Data Update Required</strong> &#8211; We need to update your install to the latest version', 'erp' ) ); ?></p>
                    <p class="submit"><a href="<?php echo esc_attr( add_query_arg( [ 'wperp_do_update' => true ], isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) ); ?>" class="wperp-update-btn button-primary"><?php esc_html_e( 'Run the updater', 'erp' ); ?></a></p>
                </div>

                <script type="text/javascript">
                    jQuery('.wperp-update-btn').click('click', function(){
                        return confirm( '<?php esc_html_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'erp' ); ?>' );
                    });
                </script>
            <?php
        } else {
            update_option( 'wp_erp_version', WPERP_VERSION );
        }
    }

    /**
     * Do all updates when Run updater btn click
     *
     * @since 1.0
     * @since 1.2.7 save plugin install date
     *
     * @return void
     */
    public function do_updates() {
        global $bg_process;
        global $bg_process_people_trn;

        $bg_process            = new ERPACCTBGProcess1_5_0();
        $bg_process_people_trn = new ERPACCTBGProcessPeopleTrn_1_5_2();

        if ( isset( $_GET['wperp_do_update'] ) && sanitize_text_field( wp_unslash( $_GET['wperp_do_update'] ) ) ) {
            $this->perform_updates();
        }
    }

    /**
     * Perform all updates
     *
     * @since 1.0
     *
     * @return void
     */
    public function perform_updates() {
        if ( ! $this->is_needs_update() ) {
            return;
        }

        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        $installed_version = get_option( 'wp_erp_version' );

        $this->enable_all_erp_modules();

        foreach ( self::$updates as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                include $path;
                update_option( 'wp_erp_version', $version );
            }
        }

        update_option( 'wp_erp_version', WPERP_VERSION );

        //save install date
        if ( false == get_option( 'wp_erp_install_date' ) ) {
            update_option( 'wp_erp_install_date', current_time( 'timestamp' ) );
        }

        $this->enable_active_erp_modules();

        $location = remove_query_arg( [ 'wperp_do_update' ], isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
        wp_redirect( $location );
        exit();
    }

    /**
     * Enable all erp modules before run the updaters
     *
     * @since 1.1.9
     *
     * @return void
     */
    private function enable_all_erp_modules() {
        // Let's remember the active modules.
        $this->active_modules = wperp()->modules->get_active_modules();

        $all_modules = wperp()->modules->get_modules();

        update_option( 'erp_modules', $all_modules );

        wperp()->load_module();
    }

    /**
     * Enable modules that were active before running the updater
     *
     * @since 1.1.9
     *
     * @return void
     */
    private function enable_active_erp_modules() {
        update_option( 'erp_modules', $this->active_modules );

        wperp()->load_module();
    }

    /**
     * Memory limit for background process
     *
     * @since 1.5.0
     *
     * @return bool
     */
    private function memory_exceeded() {
        // can be resolve by `extends WP_Background_Process class`
        $memory_limit   = $this->get_memory_limit() * 0.5; // 90% of max memory
        $current_memory = memory_get_usage( true );
        $return         = false;

        if ( $current_memory >= $memory_limit ) {
            $return = true;
        }

        return $return;
    }

    /**
     * Retrieves memory limit.
     *
     * @since 1.11.0
     *
     * @return int
     */
    protected function get_memory_limit() {
        if ( function_exists( 'ini_get' ) ) {
            $memory_limit = ini_get( 'memory_limit' );
        } else {
            // Sensible default.
            $memory_limit = '128M';
        }

        if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
            // Unlimited, set to 32GB.
            $memory_limit = '32000M';
        }

        return intval( $memory_limit ) * 1024 * 1024;
    }
}
