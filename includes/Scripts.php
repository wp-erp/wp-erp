<?php

namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Scripts and styles
 */
class Scripts {

    /*
     * Hooks
     */
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
     * @var int
     */
    protected $version;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $this->version = WPERP_VERSION;

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
        wp_register_script( 'erp-select2', $vendor . '/select2/select2.full.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-tiptip', $vendor . '/tiptip/jquery.tipTip.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-fullcalendar', $vendor . '/fullcalendar/fullcalendar' . $this->suffix . '.js', [ 'jquery', 'moment' ], $this->version, true );
        wp_register_script( 'erp-datetimepicker', $vendor . '/jquery-ui/timepicker-addon/jquery-ui-timepicker-addon.min.js', [ 'jquery', 'moment' ], $this->version, true );
        wp_register_script( 'erp-timepicker', $vendor . '/timepicker/jquery.timepicker.min.js', [ 'jquery', 'moment' ], $this->version, true );
        wp_register_script( 'erp-vuejs', $vendor . '/vue/vue' . $this->suffix . '.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-trix-editor', $vendor . '/trix/trix.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-nprogress', $vendor . '/nprogress/nprogress.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-jvectormap', $vendor . '/jvectormap/jvectormap.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-jvectormap-world-mill', $vendor . '/jvectormap/jvectormap-world-mill.js', [ 'jquery' ], $this->version, true );

        // sweet alert
        wp_register_script( 'erp-sweetalert', $vendor . '/sweetalert/sweetalert.min.js', [ 'jquery' ], $this->version, true );

        // Are you sure? JS
        wp_register_script( 'erp-are-you-sure', $vendor . '/are-you-sure/jquery.are-you-sure.js', [ 'jquery' ], $this->version, true );

        // flot chart
        wp_register_script( 'erp-flotchart', $vendor . '/flot/jquery.flot.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-time', $vendor . '/flot/jquery.flot.time.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-orerbars', $vendor . '/flot/jquery.flot.orderBars.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-pie', $vendor . '/flot/jquery.flot.pie.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-axislables', $vendor . '/flot/jquery.flot.axislabels.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-categories', $vendor . '/flot/jquery.flot.categories.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-tooltip', $vendor . '/flot/jquery.flot.tooltip.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-resize', $vendor . '/flot/jquery.flot.resize.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-valuelabel', $vendor . '/flot/jquery.flot.valuelabels.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-navigate', $vendor . '/flot/jquery.flot.navigate.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-selection', $vendor . '/flot/jquery.flot.selection.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-flotchart-stack', $vendor . '/flot/jquery.flot.stack.js', [ 'jquery' ], $this->version, true );

        // Chart js library
        wp_register_script( 'erp-chartjs', $vendor . '/chartjs/chart.min.js', [ 'jquery' ], $this->version, true );

        // core js files
        wp_register_script( 'erp-popup', $js . '/jquery-popup' . $this->suffix . '.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-script', $js . '/erp' . $this->suffix . '.js', [ 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-datepicker', 'erp-sweetalert' ], $this->version, true );
        wp_register_script( 'erp-file-upload', $js . '/upload' . $this->suffix . '.js', [ 'jquery', 'plupload-handlers' ], $this->version, true );
        wp_register_script( 'erp-admin-settings', $js . '/settings' . $this->suffix . '.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-system-status', $js . '/system-status' . $this->suffix . '.js', [ 'jquery' ], $this->version, true );

        // tether.js
        wp_register_script( 'erp-tether-main', $vendor . '/tether/tether.min.js', [ 'jquery' ], $this->version, true );
        wp_register_script( 'erp-tether-drop', $vendor . '/tether/drop.min.js', [ 'jquery' ], $this->version, true );

        // clipboard.js
        wp_register_script( 'erp-clipboard', $vendor . '/clipboard/clipboard.min.js', [ 'jquery' ], $this->version, true );

        // toastr.js
        wp_register_script( 'erp-toastr', $vendor . '/toastr/toastr.min.js', [], $this->version, true );

        // date range picker
        wp_register_script( 'erp-daterangepicker', $vendor . '/daterangepicker/daterangepicker.min.js', [ 'jquery' ], $this->version, true );
    }

    /**
     * Register all the styles
     *
     * @return void
     */
    public function register_styles() {
        $vendor = WPERP_ASSETS . '/vendor';
        $css    = WPERP_ASSETS . '/css';

        wp_register_style( 'erp-fontawesome', $vendor . '/fontawesome/font-awesome.min.css', false, $this->version );
        wp_register_style( 'erp-select2', $vendor . '/select2/select2.min.css', false, $this->version );
        wp_register_style( 'erp-tiptip', $vendor . '/tiptip/tipTip.css', false, $this->version );
        wp_register_style( 'erp-fullcalendar', $vendor . '/fullcalendar/fullcalendar' . $this->suffix . '.css', false, $this->version );
        wp_register_style( 'erp-datetimepicker', $vendor . '/jquery-ui/timepicker-addon/jquery-ui-timepicker-addon.min.css', false, $this->version );
        wp_register_style( 'erp-timepicker', $vendor . '/timepicker/jquery.timepicker.css', false, $this->version );
        wp_register_style( 'erp-trix-editor', $vendor . '/trix/trix.css', false, $this->version );
        wp_register_style( 'erp-flotchart-valuelabel-css', $vendor . '/flot/plot.css', false, $this->version );
        wp_register_style( 'erp-nprogress', $vendor . '/nprogress/nprogress.css', false, $this->version );
        wp_register_style( 'erp-jvectormap', $vendor . '/jvectormap/jvectormap.css', false, $this->version );

        // Register Pro Popup style.
        if ( ! class_exists( 'WP_ERP_Pro' ) ) {
            wp_register_style( 'add-pro-popup', $css . '/pro-popup.css', false, $this->version );
        }
        // jquery UI
        wp_register_style( 'jquery-ui', $vendor . '/jquery-ui/jquery-ui-1.9.1.custom.css' );

        // sweet alert
        wp_register_style( 'erp-sweetalert', $vendor . '/sweetalert/sweetalert.css', false, $this->version );

        // tether drop theme
        wp_register_style( 'erp-tether-drop-theme', $vendor . '/tether/drop-theme.min.css', false, $this->version );

        // toastr.js
        wp_register_style( 'erp-toastr', $vendor . '/toastr/toastr.min.css', false, $this->version );

        // core css files
        wp_register_style( 'erp-styles', $css . '/admin.css', [ 'erp-sweetalert' ], $this->version );

        // custom menu design
        wp_register_style( 'erp-custom-styles', $css . '/customs.css', false, $this->version );

        // date range picker
        wp_register_style( 'erp-daterangepicker', $vendor . '/daterangepicker/daterangepicker.min.css', false, $this->version );
    }

    /**
     * Enqueue the scripts
     *
     * @return void
     */
    public function enqueue_scripts() {
        $screen      = get_current_screen();
        $screen_base = isset( $screen->base ) ? $screen->base : false;
        $hook        = str_replace( sanitize_title( __( 'HR Management', 'erp' ) ), 'hr-management', $screen_base );

        wp_enqueue_script( 'erp-select2' );
        wp_enqueue_script( 'erp-popup' );
        wp_enqueue_script( 'erp-script' );
        wp_enqueue_script( 'erp-are-you-sure' );
        wp_enqueue_media();

        wp_localize_script( 'erp-script', 'wpErp', [
            'nonce'           => wp_create_nonce( 'erp-nonce' ),
            'set_logo'        => __( 'Set company logo', 'erp' ),
            'upload_logo'     => __( 'Upload company logo', 'erp' ),
            'remove_logo'     => __( 'Remove company logo', 'erp' ),
            'update_location' => __( 'Update Location', 'erp' ),
            'create'          => __( 'Create', 'erp' ),
            'update'          => __( 'Update', 'erp' ),
            'formUnsavedMsg'  => __( 'You didn\'t save your changes!', 'erp' ),
            'confirmMsg'      => __( 'Are you sure?', 'erp' ),
            'ajaxurl'         => admin_url( 'admin-ajax.php' ),
            'plupload'        => [
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'erp_featured_img' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => [ [ 'title' => __( 'Allowed Files', 'erp' ), 'extensions' => '*' ] ],
                'multipart'        => true,
                'urlstream_upload' => true,
            ],
        ] );

        wp_enqueue_script( 'erp-menu', WPERP_ASSETS . '/js/erp-menu.js', [], gmdate( 'Ymd' ), true );

        // load country/state JSON on new company page
        if ( erp_is_contacts_page() ) {
            wp_enqueue_script( 'post' );

            $country = \WeDevs\ERP\Countries::instance();
            wp_localize_script( 'erp-script', 'wpErpCountries', $country->load_country_states() );
        }

        if ( erp_is_current_page( 'erp-hr', 'people', 'employee' ) || erp_is_current_page( 'erp-hr', 'my-profile' ) || ( isset( $_GET['page'] ) && 'erp-company' === $_GET['page'] ) ) {
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

        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        if ( ! empty( $page ) && ( 'erp-hr' === $page || 'erp-crm' === $page || 'erp-settings' === $page ) ) {
            wp_enqueue_style( 'erp-custom-styles' );
        }
    }
}
