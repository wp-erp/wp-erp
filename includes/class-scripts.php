<?php
namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Scripts and styles
 */
class Scripts {

    use Hooker;

    /**
     * Script and style suffix
     *
     * @var string
     */
    protected $suffix;

    /**
     * Script version number
     *
     * @var integer
     */
    protected $version;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $this->version = '20150314';

        $this->action( 'admin_enqueue_scripts', 'scripts_handler' );
    }

    /**
     * Register and enqueue scripts and styles
     *
     * @return void
     */
    public function scripts_handler() {
        $this->register_scripts();
        $this->register_styles();

        $this->enqueue_scripts();
        $this->enqueue_styles();
    }

    public function register_scripts() {

        // wp_register_script( $handle, $src, $deps, $ver, $in_footer );
        $vendor = WPERP_ASSETS . '/vendor';
        $js     = WPERP_ASSETS . '/js';

        // register vendors first
        wp_register_script( 'erp-select2', $vendor . '/select2/select2.full.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-tiptip', $vendor . '/tiptip/jquery.tipTip.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-momentjs', $vendor . '/moment/moment.min.js', false, $this->version, true );
        wp_register_script( 'erp-fullcalendar', $vendor . '/fullcalendar/fullcalendar' . $this->suffix . '.js', array( 'jquery', 'erp-momentjs' ), $this->version, true );
        wp_register_script( 'erp-timepicker', $vendor . '/timepicker/jquery.timepicker.min.js', array( 'jquery', 'erp-momentjs' ), $this->version, true );
        wp_register_script( 'erp-vuejs', $vendor . '/vue/vue' . $this->suffix . '.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-trix-editor', $vendor . '/trix/trix.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-nprogress', $vendor . '/nprogress/nprogress.js', array( 'jquery' ), $this->version, true );

        // sweet alert
        wp_register_script( 'erp-sweetalert', $vendor . '/sweetalert/sweetalert.min.js', array( 'jquery' ), $this->version, true );

        // flot chart
        wp_register_script( 'erp-flotchart', $vendor . '/flot/jquery.flot.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-time', $vendor . '/flot/jquery.flot.time.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-orerbars', $vendor . '/flot/jquery.flot.orderBars.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-pie', $vendor . '/flot/jquery.flot.pie.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-axislables', $vendor . '/flot/jquery.flot.axislabels.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-tooltip', $vendor . '/flot/jquery.flot.tooltip.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-resize', $vendor . '/flot/jquery.flot.resize.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-valuelabel', $vendor . '/flot/jquery.flot.valuelabels.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-navigate', $vendor . '/flot/jquery.flot.navigate.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-flotchart-selection', $vendor . '/flot/jquery.flot.selection.js', array( 'jquery' ), $this->version, true );

        // core js files
        wp_register_script( 'erp-popup', $js . '/jquery-popup' . $this->suffix . '.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-script', $js . '/erp' . $this->suffix . '.js', array( 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-datepicker' ), $this->version, true );
        wp_register_script( 'erp-file-upload', $js . '/upload' . $this->suffix . '.js', array( 'jquery', 'plupload-handlers' ), $this->version, true );
        wp_register_script( 'erp-admin-settings', $js . '/settings' . $this->suffix . '.js', array( 'jquery' ), $this->version, true );

        // tether.js
        wp_register_script( 'erp-tether-main', $vendor . '/tether/tether.min.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'erp-tether-drop', $vendor . '/tether/drop.min.js', array( 'jquery' ), $this->version, true );

        // clipboard.js
        wp_register_script( 'erp-clipboard', $vendor . '/clipboard/clipboard.min.js', array( 'jquery' ), $this->version, true );
    }

    /**
     * Register all the styles
     *
     * @return void
     */
    public function register_styles() {
        $vendor = WPERP_ASSETS . '/vendor';
        $css     = WPERP_ASSETS . '/css';

        wp_register_style( 'erp-fontawesome', $vendor . '/fontawesome/font-awesome.min.css', false, $this->version );
        wp_register_style( 'erp-select2', $vendor . '/select2/select2.min.css', false, $this->version );
        wp_register_style( 'erp-tiptip', $vendor . '/tiptip/tipTip.css', false, $this->version );
        wp_register_style( 'erp-fullcalendar', $vendor . '/fullcalendar/fullcalendar' . $this->suffix . '.css', false, $this->version );
        wp_register_style( 'erp-timepicker', $vendor . '/timepicker/jquery.timepicker.css', false, $this->version );
        wp_register_style( 'erp-trix-editor', $vendor . '/trix/trix.css', false, $this->version );
        wp_register_style( 'erp-flotchart-valuelabel-css', $vendor . '/flot/plot.css', false, $this->version );
        wp_register_style( 'erp-nprogress', $vendor . '/nprogress/nprogress.css', false, $this->version );

        // jquery UI
        wp_register_style( 'jquery-ui', $vendor . '/jquery-ui/jquery-ui-1.9.1.custom.css' );

        // sweet alert
        wp_register_style( 'erp-sweetalert', $vendor . '/sweetalert/sweetalert.css', false, $this->version );

        // tether drop theme
        wp_register_style( 'erp-tether-drop-theme', $vendor . '/tether/drop-theme.min.css', false, $this->version );

        // core css files
        wp_register_style( 'erp-styles', $css . '/admin.css', false, $this->version );
    }

    /**
     * Enqueue the scripts
     *
     * @return void
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        $hook = str_replace( sanitize_title( __( 'HR Management', 'erp' ) ) , 'hr-management', $screen->base );

        wp_enqueue_script( 'erp-select2' );
        wp_enqueue_script( 'erp-popup' );
        wp_enqueue_script( 'erp-script' );

        wp_localize_script( 'erp-script', 'wpErp', array(
            'nonce'           => wp_create_nonce( 'erp-nonce' ),
            'ajaxurl'         => admin_url( 'admin-ajax.php' ),
            'set_logo'        => __( 'Set company logo', 'erp' ),
            'upload_logo'     => __( 'Upload company logo', 'erp' ),
            'remove_logo'     => __( 'Remove company logo', 'erp' ),
            'update_location' => __( 'Update Location', 'erp' ),
            'create'          => __( 'Create', 'erp' ),
            'update'          => __( 'Update', 'erp' ),
            'confirmMsg'      => __( 'Are you sure?', 'wpuf' ),
            'ajaxurl'         => admin_url( 'admin-ajax.php' ),
            'plupload'        => array(
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'erp_featured_img' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                'multipart'        => true,
                'urlstream_upload' => true,
            )
        ) );

        // load country/state JSON on new company page
        if ( 'toplevel_page_erp-company' == $hook || isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'new', 'edit' ) ) ) {
            wp_enqueue_script( 'post' );
            wp_enqueue_media();

            $country = \WeDevs\ERP\Countries::instance();
            wp_localize_script( 'erp-script', 'wpErpCountries', $country->load_country_states() );
        }

        if ( 'hr-management_page_erp-hr-employee' == $hook ) {
            $country = \WeDevs\ERP\Countries::instance();
            wp_localize_script( 'erp-script', 'wpErpCountries', $country->load_country_states() );
        }
    }

    /**
     * Enqueue the stylesheet
     *
     * @return void
     */
    public function enqueue_styles() {
        wp_enqueue_style( 'erp-fontawesome' );
        wp_enqueue_style( 'erp-select2' );
        wp_enqueue_style( 'jquery-ui' );

        wp_enqueue_style( 'erp-styles' );
    }
}
