<?php

namespace WeDevs\ERP\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * ERP core CLI commands
 *
 * @since 1.2.2
 */
class Commands extends WP_CLI_Command {

    /**
     * Activate single or multiple ERP modules
     *
     * ## OPTIONS
     *
     * <comma_separated_module_names>
     * : Comma separated ERP module names
     *
     * ## EXAMPLES
     *
     *     # Activate single module
     *     $ wp erp module activate hrm
     *     Success: Activated module hrm
     *
     *     # Activate multiple modules
     *     $ wp erp module activate crm,accounting
     *     Success: Activated modules crm, accounting
     *
     * @since 1.2.2
     *
     * @return void
     */
    public function module_activate( $args ) {
        list( $modules ) = $args;
        $modules         = explode( ',', $modules );

        $activated = wperp()->modules->activate_modules( $modules );

        if ( is_wp_error( $activated ) ) {
            WP_CLI::error( $activated->get_error_message() );
        }

        $count   = count( $modules );
        $message = sprintf( _n( 'Activated module %s', 'Activated modules %s', $count, 'erp' ), implode( ', ', $modules ) );

        WP_CLI::success( $message );
    }

    /**
     * Deactivate single or multiple ERP modules
     *
     * ## OPTIONS
     *
     * <comma_separated_module_names>
     * : Comma separated ERP module names
     *
     * ## EXAMPLES
     *
     *     # Deactivate single module
     *     $ wp erp module deactivate hrm
     *     Success: Deactivate module hrm
     *
     *     # Deactivate multiple modules
     *     $ wp erp module deactivate crm,accounting
     *     Success: Deactivate modules crm, accounting
     *
     * @since 1.2.2
     *
     * @return void
     */
    public function module_deactivate( $args ) {
        list( $modules ) = $args;
        $modules         = explode( ',', $modules );

        $deactivated = wperp()->modules->deactivate_modules( $modules );

        if ( is_wp_error( $deactivated ) ) {
            WP_CLI::error( $deactivated->get_error_message() );
        }

        $count   = count( $modules );
        $message = sprintf( _n( 'Deactivated module %s', 'Deactivated modules %s', $count, 'erp' ), implode( ', ', $modules ) );

        WP_CLI::success( $message );
    }
}

WP_CLI::add_command( 'erp module activate', [ '\WeDevs\ERP\CLI\Commands', 'module_activate' ] );
WP_CLI::add_command( 'erp module deactivate', [ '\WeDevs\ERP\CLI\Commands', 'module_deactivate' ] );