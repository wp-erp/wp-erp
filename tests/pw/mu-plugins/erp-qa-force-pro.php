<?php
/**
 * Plugin Name: WP ERP QA — Force Pro
 * Description: Test-only must-use plugin. When the option `erp_qa_force_pro` is
 *   truthy it forces the WP ERP Pro license gates open so every pro module loads
 *   (admin menus + REST + tables) in the wp-env QA site. This mirrors Dokan's
 *   lite/pro toggle: ERP_PRO=true → setup sets the flag → full pro surface;
 *   ERP_PRO unset → flag cleared → the site behaves as lite.
 *
 * It works on the READ side (`option_erp_pro_license_status`) so the forged
 * status survives the periodic license re-check, crons and wp-env restarts —
 * nothing in erp-pro can wipe it for the duration of a run.
 *
 * This file lives in the test suite and is mapped into wp-content/mu-plugins by
 * .wp-env.json. It is never shipped with the plugin.
 *
 * @package wp-erp-qa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The full erp-pro module registry as id => path, mirroring
 * \WeDevs\ERP_PRO\Module::get_all_modules(). The license gate in
 * Module::load_active_modules() skips any `is_pro === false` module whose
 * `path` is not in get_licensed_extensions(), so every path must be granted;
 * the ids are also written to `erp_pro_active_modules` so each module activates.
 */
function erp_qa_force_pro_modules() {
	return array(
		'inventory'            => 'accounting/inventory',
		'payment_gateway'      => 'accounting/payment-gateway',
		'woocommerce'          => 'accounting/woocommerce',
		'deals'                => 'crm/deals',
		'asset_management'     => 'hrm/asset-management',
		'attendance'           => 'hrm/attendance',
		'custom_field_builder' => 'hrm/custom-field-builder',
		'document_manager'     => 'hrm/document-manager',
		'hr_training'          => 'hrm/hr-training',
		'payroll'              => 'hrm/payroll',
		'recruitment'          => 'hrm/recruitment',
		'reimbursement'        => 'hrm/reimbursement',
		'sms_notification'     => 'hrm/sms-notification',
		'workflow'             => 'hrm/workflow',
		'advanced_leave'       => 'pro/advanced-leave',
		'awesome_support'      => 'pro/awesome-support',
		'gravity_forms'        => 'pro/gravity_forms',
		'help_scout'           => 'pro/help-scout',
		'hr_frontend'          => 'pro/hr-frontend',
		'hubspot'              => 'pro/hubspot',
		'mailchimp'            => 'pro/mailchimp',
		'salesforce'           => 'pro/salesforce',
		'zendesk'              => 'pro/zendesk',
	);
}

function erp_qa_force_pro_module_paths() {
	return array_values( erp_qa_force_pro_modules() );
}

/**
 * Force a valid, all-extensions, high-user-cap license status when the QA flag
 * is set. Applied on every get_option( 'erp_pro_license_status' ) read.
 *
 * @param mixed $value Stored option value.
 *
 * @return object
 */
function erp_qa_force_pro_license_status( $value ) {
	if ( ! get_option( 'erp_qa_force_pro' ) ) {
		return $value;
	}

	if ( ! is_object( $value ) ) {
		$value = new stdClass();
	}

	$value->success    = true;
	$value->license    = 'valid';
	$value->users      = 99999;
	$value->extensions = erp_qa_force_pro_module_paths();

	if ( empty( $value->subscription_expire_date ) ) {
		$value->subscription_expire_date = '2030-01-01 00:00:00';
	}

	return $value;
}
add_filter( 'option_erp_pro_license_status', 'erp_qa_force_pro_license_status', 99 );
add_filter( 'default_option_erp_pro_license_status', 'erp_qa_force_pro_license_status', 99 );

/**
 * One-time proper installation of every pro module.
 *
 * Simply writing erp_pro_active_modules is NOT enough — each module installs its
 * DB tables and role caps inside its `erp_pro_activated_module_*` hook, which
 * only fires when erp-pro's own Module::activate_modules() sees the module as
 * newly activated. So here we run that real activation path once: clear the
 * active list, then activate_modules() the full set so every install hook fires
 * (creating tables like wp_erp_crm_deals_*, payroll, attendance shifts, etc.).
 *
 * Gated by the `erp_qa_pro_installed` marker so it runs a single time; the
 * Playwright @pro setup deletes that marker to force a clean (re)install. Runs on
 * `erp_loaded` (end of the WP ERP boot) so erp-pro and the forced license are
 * fully available. Keeping it in PHP means the setup only flips scalar options
 * and never has to push fragile multi-layer wp-cli eval through wp-env.
 */
function erp_qa_force_pro_install() {
	if ( ! get_option( 'erp_qa_force_pro' ) || get_option( 'erp_qa_pro_installed' ) ) {
		return;
	}

	if ( ! function_exists( 'wp_erp_pro' ) || ! class_exists( '\WeDevs\ERP_PRO\Module' ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Best-effort genuine activation against wperp.com using the recorded credentials,
	// so production license code paths run. Non-fatal: the force-filter validates the
	// license regardless, and the test site's ~240 users exceed the real cap anyway.
	$creds = get_option( 'erp_pro_license' );
	if ( is_array( $creds ) && ! empty( $creds['key'] ) ) {
		try {
			@wp_erp_pro()->update->activation( 'activate_license' );
		} catch ( \Throwable $e ) {
			// ignore — the QA force overrides the gates below.
		}
	}

	$ids = array_keys( erp_qa_force_pro_modules() );

	// erp-pro's Module::activate_modules() SKIPS any is_crm / is_acc / is_hrm pro
	// module whose parent FREE core module is inactive (includes/Module.php ~L856-897).
	// HRM is on by default, but CRM + Accounting are not — and at this early hook
	// `wperp()->modules->is_module_active()` does not yet reflect a DB-activated core
	// module — so without this every CRM/Accounting pro module (deals, inventory, the
	// CRM integrations, …) silently fails to activate and only the ~11 HRM modules
	// stick. Activate the core parents HERE first (refreshes the in-request state) so
	// the full available pro set can install.
	if ( function_exists( 'wperp' ) && isset( wperp()->modules ) ) {
		wperp()->modules->activate_modules( array( 'crm', 'accounting' ) );
	}

	// Force every module to be treated as newly activated so its install hook fires.
	update_option( 'erp_pro_active_modules', array() );

	$module = \WeDevs\ERP_PRO\Module::init();
	$module->activate_modules( $ids );

	update_option( 'erp_qa_pro_installed', 1 );
}
add_action( 'erp_loaded', 'erp_qa_force_pro_install', 5 );
