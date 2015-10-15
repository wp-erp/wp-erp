<?php
namespace WeDevs\ERP\Admin;

/**
 * Administration Module Class
 *
 * @package payroll
 */
class Admin_Module {

	function __construct() {
		$this->output();
	}

	function output() {
		require_once WPERP_VIEWS . '/module.php';
	}
}