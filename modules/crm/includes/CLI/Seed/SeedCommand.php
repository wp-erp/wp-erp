<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;
use WP_CLI_Command;

class SeedCommand extends WP_CLI_Command {

    /**
     * Seed the CRM module with sample data.
     *
     * Generates contact groups, companies, contacts, activities,
     * and deals (Pro feature).
     *
     * ## OPTIONS
     *
     * [--skip=<steps>]
     * : Comma-separated list of steps to skip.
     * ---
     * default:
     * ---
     *
     * [--only=<steps>]
     * : Only run specified steps (comma-separated).
     * ---
     * default:
     * ---
     *
     * [--contacts=<count>]
     * : Number of contacts to create.
     * ---
     * default: 50
     * ---
     *
     * [--activities=<count>]
     * : Number of activities to create.
     * ---
     * default: 100
     * ---
     *
     * [--deals=<count>]
     * : Number of deals to create.
     * ---
     * default: 30
     * ---
     *
     * ## EXAMPLES
     *
     *     wp crm seed
     *     wp crm seed --skip=deals
     *     wp crm seed --only=contacts,activities
     *     wp crm seed --contacts=100 --activities=200
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        wp_set_current_user( 1 );

        $steps = [
            'contact-groups' => 'Contact Groups',
            'companies'      => 'Companies',
            'contacts'       => 'Contacts',
            'activities'     => 'Activities',
            'deals'          => 'Deals (Pro)',
        ];

        $skip = ! empty( $assoc_args['skip'] ) ? explode( ',', $assoc_args['skip'] ) : [];
        $only = ! empty( $assoc_args['only'] ) ? explode( ',', $assoc_args['only'] ) : array_keys( $steps );

        WP_CLI::log( '' );
        WP_CLI::log( '=== WP ERP CRM Seeder ===' );
        WP_CLI::log( '' );

        foreach ( $steps as $key => $label ) {
            if ( in_array( $key, $skip, true ) || ! in_array( $key, $only, true ) ) {
                continue;
            }

            WP_CLI::log( "--- Seeding: {$label} ---" );

            $extra_args = '';

            if ( $key === 'contacts' && ! empty( $assoc_args['contacts'] ) ) {
                $extra_args = ' --count=' . (int) $assoc_args['contacts'];
            }

            if ( $key === 'activities' && ! empty( $assoc_args['activities'] ) ) {
                $extra_args = ' --count=' . (int) $assoc_args['activities'];
            }

            if ( $key === 'deals' && ! empty( $assoc_args['deals'] ) ) {
                $extra_args = ' --deals=' . (int) $assoc_args['deals'];
            }

            WP_CLI::runcommand( "crm seed:{$key}{$extra_args}", [ 'launch' => false ] );

            WP_CLI::log( '' );
        }

        WP_CLI::success( 'CRM seed data generation complete!' );
    }
}

WP_CLI::add_command( 'crm seed', __NAMESPACE__ . '\\SeedCommand' );
