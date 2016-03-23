<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * CRM Integration handler class
 */
class Integration {

    use Hooker;

    function __construct() {
        $this->filter( 'erp_integration_classes', 'register_integrations' );
    }

    function register_integrations( $integrations ) {
        // Put CRM Integration here.
        return apply_filters( 'erp_crm_integration_classes', $integrations );
    }
}