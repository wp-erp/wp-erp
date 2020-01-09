<?php
namespace WeDevs\ERP;

use \WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Integration Class
 */
class Integration extends ERP_Settings_Page {
    /**
     * Integration instances.
     */
    public $integrations;

    /**
     * Form option fields.
     *
     * @var array
     */
    public $form_fields = array();

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
     * Class constructor.
     */
    public function __construct() {
        // Let 3rd parties unhook the above via this hook
        do_action( 'erp_integration', $this );
    }

    /**
     * Initialize integrations.
     *
     * @return array
     */
    function init_integrations() {
        $this->integrations = apply_filters( 'erp_integration_classes', $this->integrations );
    }

    /**
     * Return the integration classes - used in admin to load settings.
     *
     * @return array
     */
    public function get_integrations() {
        return $this->integrations;
    }

    /**
     * Get an registered integration instance
     *
     * @param  string  $class_name
     *
     * @return \Integration|false
     */
    public function get_integration( $class_name ) {
        if ( $this->integrations && array_key_exists( $class_name, $this->integrations ) ) {
            return $this->integrations[ $class_name ];
        }

        return false;
    }

    /**
     * Get saved option id
     *
     * @return string
     */
    public function get_option_id() {
        return 'erp_integration_settings_' . $this->id;
    }

    /**
     * Get the form fields after they are initialized.
     * @return array of options
     */
    public function get_form_fields() {
        return apply_filters( 'erp_settings_integration_form_fields_' . $this->id, $this->form_fields );
    }

    /**
     * Generate settings html.
     *
     * @return void
     */
    function generate_settings_html() {
        $settings = $this->get_form_fields();
        $this->output_fields( $settings );
    }

    /**
     * Get integration setting by key
     *
     * @param  string  $option
     * @param  string  $default
     *
     * @return string
     */
    public function get_setting( $option, $default = '' ) {
        $settings = get_option( 'erp_settings_erp-integration', [] );

        if ( array_key_exists( $option, $settings ) ) {
            return $settings[ $option ];
        }

        return $default;
    }

    /**
     * Get the admin options of this integration.
     *
     * @return void
     */
    public function admin_options() {
        ?>
        <h3><?php echo esc_html( $this->get_title() ); ?></h3>
        <?php echo wp_kses_post( wpautop( $this->get_description() ) ); ?>

        <?php
            /**
             * erp_email_settings_before action hook.
             *
             * @param string $integration The integration object
             */
            do_action( 'erp_integration_settings_before', $this );
        ?>

        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>

        <?php
            /**
             * erp_integration_settings_after action hook.
             *
             * @param string $integration The integration object
             */
            do_action( 'erp_integration_settings_after', $this );
        ?>
        <?php
    }

}
