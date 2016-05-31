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
                'title'       => __( 'HR Management', 'erp' ),
                'slug'        => 'erp-hrm',
                'description' => __( 'Human Resource Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\HRM\Human_Resource',
                'modules'     => apply_filters( 'erp_hr_modules', [ ] )
            ],
            'crm' => [
                'title'       => __( 'CR Management', 'erp' ),
                'slug'        => 'erp-crm',
                'description' => __( 'Client Resource Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\CRM\Customer_Relationship',
                'modules'     => apply_filters( 'erp_crm_modules', [ ] )
            ],
            'accounting' => [
                'title'       => __( 'Accounting', 'erp' ),
                'slug'        => 'erp-accounting',
                'description' => __( 'Accounting Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\Accounting\Accounting',
                'modules'     => apply_filters( 'erp_ac_modules', [ ] )
            ]
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

    /**
     * Get module status
     *
     * @param string $module_key
     *
     * @since 0.1
     *
     * @return boolen
     */
    public function is_module_active( $module_key ) {
        $modules = $this->get_active_modules();

        if ( array_key_exists( $module_key, $modules ) ) {
            return true;
        }

        return false;
    }

    /**
     * Get active modules
     *
     * @since 0.1
     *
     * @return array
     */
    public function get_active_modules() {
        return get_option( 'erp_modules', [] );
    }

    /**
     * Get inactive modules
     *
     * @since 0.1
     *
     * @return array
     */
    public function get_inactive_modules() {
        $all_modules    = $this->get_modules();
        $active_modules = $this->get_active_modules();

        foreach ( $active_modules as $key => $module ) {
            unset( $all_modules[$key] );
        }

        return $all_modules;
    }

    /**
     * Get modules by query pram
     *
     * @param string $tab
     *
     * @since 0.1
     *
     * @return array
     */
    public function get_query_modules( $tab = false ) {

        switch ( $tab ) {
            case 'active':
                return $this->get_active_modules();
                break;

            case 'inactive':
                return $this->get_inactive_modules();
                break;

            default:
                return $this->get_modules();
                break;
        }
    }

}
