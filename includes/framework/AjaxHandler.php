<?php

namespace WeDevs\ERP\Framework;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 */
class AjaxHandler {
    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for Framework
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function __construct () {
        add_action( 'admin_init', [ $this, 'init_actions' ] );
    }

    /**
     * Init all actions
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function init_actions () {
        new \WeDevs\ERP\Settings\Ajax();
    }
}
