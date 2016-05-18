<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Installer Class
 *
 * @package ERP
 */
class Updates {

    use Hooker;

    /** @var array DB updates that need to be run */
    private static $updates = [
        '1.1' => 'updates/update-1.1.php',
    ];

    /**
     * Binding all events
     *
     * @since 0.1
     *
     * @return void
     */
    function __construct() {
        $this->action( 'admin_notices', 'show_update_notice' );
        $this->action( 'admin_init', 'do_updates' );
    }

    /**
     * Check if need any update
     *
     * @since 1.0
     *
     * @return boolean
     */
    public function is_needs_update() {
        $installed_version = get_option( 'wp_erp_ac_version', '1.0' );

        // may be it's the first install
        if ( ! $installed_version ) {
            return false;
        }

        if ( version_compare( $installed_version, WPERP_ACCOUNTING_VERSION, '<' ) ) {
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
        if ( ! $this->is_needs_update() ) {
            return;
        }
        ?>
        <div id="message" class="updated">
            <p><?php _e( '<strong>WP ERP - Accounting Data Update Required</strong> &#8211; We need to update your install to the latest version', 'accounting' ); ?></p>
            <p class="submit"><a href="<?php echo add_query_arg( [ 'wperp_ac_do_update' => true ], $_SERVER['REQUEST_URI'] ); ?>" class="wperp-update-btn button-primary"><?php _e( 'Run the updater', 'erp' ); ?></a></p>
        </div>

        <script type="text/javascript">
            jQuery('.wperp-update-btn').click('click', function(){
                return confirm( '<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'accounting' ); ?>' );
            });
        </script>
        <?php
    }

    /**
     * Do all updates when Run updater btn click
     *
     * @since 1.0
     *
     * @return void
     */
    public function do_updates() {
        if ( isset( $_GET['wperp_ac_do_update'] ) && $_GET['wperp_ac_do_update'] ) {
            $this->perform_updates();
            $this->update_version();
        }
    }

    function update_version() {
        // update to latest version
        $latest_version = erp_ac_get_version();
        update_option( 'wp_erp_ac_version', $latest_version );
        update_option( 'wp_erp_ac_db_version', $latest_version );
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

        $installed_version = get_option( 'wp_erp_ac_version' );

        foreach ( self::$updates as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                include $path;
                update_option( 'wp_erp_ac_version', $version );
            }
        }

        // finally update to the latest version
        update_option( 'wp_erp_ac_version', WPERP_ACCOUNTING_VERSION );

        $location = remove_query_arg( ['wperp_ac_do_update'], $_SERVER['REQUEST_URI'] );
        wp_redirect( $location );
        exit();
    }


}
