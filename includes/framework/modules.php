<?php
namespace WeDevs\ERP\Framework;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Modules Class
 *
 * @package WeDevs\ERP\Framework
 */
class Modules {

    use Hooker;

    /**
     * Hold the modules
     *
     * @var
     */
    protected $modules;

    /**
     * initialize
     */
    public function __construct() {
        $this->init_modules();

        $this->action( 'erp_switch_redirect_to', 'module_switch_redirect', 10, 2 );
    }

    /**
     * Initialize the modules
     *
     * @return void
     */
    public function init_modules() {
        $this->modules = [
            'hrm' => [
                'title'    => __( 'HR Management', 'wp-erp' ),
                'slug'     => 'erp-hrm',
                'callback' => '\WeDevs\ERP\HRM\Human_Resource',
                'modules'  => apply_filters( 'erp_hr_modules', [ ] )
            ],
        ];
    }

    /**
     * Get all the registered modules
     *
     * @return mixed|void
     */
    public function get_modules() {
        return apply_filters( 'erp_get_modules', $this->modules );
    }

    /**
     * Get a module
     *
     * @param $module
     *
     * @return bool
     */
    public function get_module( $module ) {
        if ( array_key_exists( $module, $this->modules ) ) {
            return $this->modules[ $module ];
        }

        return false;
    }

    /**
     * Get the first module as default module
     *
     * @return mixed
     */
    public function get_default_module() {
        return reset( $this->modules );
    }

    /**
     * Get the current mode of the admin UI
     *
     * @return bool|mixed
     */
    public function get_current_module() {
        global $current_user;

        $mode = get_user_meta( $current_user->ID, '_erp_mode', true );

        if ( !empty( $mode ) && $this->get_module( $mode ) ) {
            return $this->get_module( $mode );
        }

        return $this->get_default_module();
    }

    /**
     * Redirect to the CRM/HRM dashboard page when switching from admin menu bar
     *
     * @param $url
     * @param $new_mode
     *
     * @return string|void
     */
    public function module_switch_redirect( $url, $new_mode ) {
        if ( 'crm' == $new_mode ) {
            return admin_url( 'admin.php?page=erp-sales' );
        } elseif ( 'hrm' == $new_mode ) {
            return admin_url( 'admin.php?page=erp-hr' );
        }

        return $url;
    }
}