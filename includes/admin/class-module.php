<?php

namespace WeDevs\ERP\Admin;

/**
 * Administration Module Class
 */
class Admin_Module {
    public function __construct() {
        $this->output();
    }

    public function output() {
        require_once WPERP_VIEWS . '/module.php';
    }
}
