<?php
namespace WeDevs\ERP;

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
        '1.0' => 'updates/update-1.0.php',
        '1.1.0' => 'updates/update-1.1.0.php',
        '1.1.1' => 'updates/update-1.1.1.php',
        '1.1.2' => 'updates/update-1.1.2.php',
        '1.1.3' => 'updates/update-1.1.3.php',
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

        if ( ! is_null( WPERP_VERSION ) && version_compare( WPERP_VERSION, max( array_keys( self::$updates ) ), '<=' ) ) {
            ?>
                <div id="message" class="updated">
                    <p><?php _e( '<strong>WP ERP Data Update Required</strong> &#8211; We need to update your install to the latest version', 'erp' ); ?></p>
                    <p class="submit"><a href="<?php echo add_query_arg( [ 'wperp_do_update' => true ], $_SERVER['REQUEST_URI'] ); ?>" class="wperp-update-btn button-primary"><?php _e( 'Run the updater', 'erp' ); ?></a></p>
                </div>

                <script type="text/javascript">
                    jQuery('.wperp-update-btn').click('click', function(){
                        return confirm( '<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'erp' ); ?>' );
                    });
                </script>
            <?php
        } else {
            update_option( 'wp_erp_version', WPERP_VERSION );
        }

        ?>
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
        if ( isset( $_GET['wperp_do_update'] ) && $_GET['wperp_do_update'] ) {
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

        $installed_version = get_option( 'wp_erp_version' );

        foreach ( self::$updates as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                include $path;
                update_option( 'wp_erp_version', $version );
            }
        }

        $location = remove_query_arg( ['wperp_do_update'], $_SERVER['REQUEST_URI'] );
        wp_redirect( $location );
        exit();
    }
}
