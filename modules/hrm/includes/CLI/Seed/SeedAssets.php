<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedAssets extends AbstractSeeder {

    /**
     * Generate assets and allocate to employees (Pro feature).
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of asset items to create.
     * ---
     * default: 30
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:assets
     *     wp hr seed:assets --count=50
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        if ( ! $this->table_exists( 'erp_hr_assets' ) ) {
            WP_CLI::warning( 'Asset management module not active (table erp_hr_assets not found). Skipping.' );
            return;
        }

        global $wpdb;

        $count        = (int) ( $assoc_args['count'] ?? 30 );
        $employee_ids = $this->get_employee_user_ids();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first.' );
        }

        // Create asset categories.
        $cat_table  = $wpdb->prefix . 'erp_hr_assets_category';
        $categories = DataProvider::asset_categories();
        $cat_ids    = [];

        $progress = $this->progress( 'Creating asset categories', count( $categories ) );

        foreach ( $categories as $cat_name ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$cat_table} WHERE cat_name = %s",
                    $cat_name
                )
            );

            if ( $existing ) {
                $cat_ids[ $cat_name ] = $existing;
            } else {
                $wpdb->insert(
                    $cat_table,
                    [ 'cat_name' => $cat_name ],
                    [ '%s' ]
                );
                $cat_ids[ $cat_name ] = $wpdb->insert_id;
            }

            $progress->tick();
        }

        $progress->finish();

        // Create assets.
        $asset_table   = $wpdb->prefix . 'erp_hr_assets';
        $asset_defs    = DataProvider::assets();
        $progress      = $this->progress( 'Creating assets', $count );
        $created_ids   = [];
        $serial_counter = 1000;

        for ( $i = 0; $i < $count; $i++ ) {
            $def         = $asset_defs[ $i % count( $asset_defs ) ];
            $category_id = isset( $cat_ids[ $def['category'] ] ) ? $cat_ids[ $def['category'] ] : 1;
            $serial_counter++;

            $reg_date  = DataProvider::random_date_between(
                date( 'Y-m-d', strtotime( '-2 years' ) ),
                date( 'Y-m-d' )
            );
            $exp_date  = date( 'Y-m-d', strtotime( $reg_date . ' +3 years' ) );
            $warr_date = date( 'Y-m-d', strtotime( $reg_date . ' +1 year' ) );

            $wpdb->insert(
                $asset_table,
                [
                    'parent'        => 0,
                    'category_id'   => $category_id,
                    'item_group'    => $def['group'],
                    'asset_type'    => 'single',
                    'item_code'     => strtoupper( substr( $def['group'], 0, 3 ) ) . '-' . $serial_counter,
                    'model_no'      => $def['model'],
                    'manufacturer'  => $def['manufacturer'],
                    'item_serial'   => 'SN-' . $serial_counter,
                    'item_desc'     => $def['group'] . ' - ' . $def['manufacturer'] . ' ' . $def['model'],
                    'price'         => $def['price'],
                    'date_reg'      => $reg_date,
                    'date_expiry'   => $exp_date,
                    'date_warranty' => $warr_date,
                    'allottable'    => 'yes',
                    'status'        => 'stock',
                ],
                [ '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s' ]
            );

            $created_ids[] = $wpdb->insert_id;
            $progress->tick();
        }

        $progress->finish();

        // Allocate ~60% of assets to employees.
        $history_table  = $wpdb->prefix . 'erp_hr_assets_history';
        $allocate_count = (int) ( $count * 0.6 );
        $progress       = $this->progress( 'Allocating assets', $allocate_count );

        shuffle( $created_ids );

        for ( $i = 0; $i < $allocate_count && $i < count( $created_ids ); $i++ ) {
            $asset_id = $created_ids[ $i ];
            $emp_id   = DataProvider::random_element( $employee_ids );

            $asset = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$asset_table} WHERE id = %d", $asset_id )
            );

            if ( ! $asset ) {
                $progress->tick();
                continue;
            }

            $given_date  = DataProvider::random_date_between(
                $asset->date_reg,
                date( 'Y-m-d' )
            );
            $return_date = date( 'Y-m-d', strtotime( $given_date . ' +1 year' ) );

            $wpdb->insert(
                $history_table,
                [
                    'category_id'          => $asset->category_id,
                    'item_group'           => $asset->id,
                    'item_id'              => $asset->id,
                    'allotted_to'          => $emp_id,
                    'is_returnable'        => 'yes',
                    'date_given'           => $given_date,
                    'date_return_proposed'  => $return_date,
                    'status'               => 'allotted',
                ],
                [ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
            );

            // Update asset status.
            $wpdb->update(
                $asset_table,
                [ 'status' => 'allotted' ],
                [ 'id' => $asset_id ],
                [ '%s' ],
                [ '%d' ]
            );

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success(
            sprintf( 'Created %d asset categories, %d assets, allocated %d to employees.',
                count( $cat_ids ), count( $created_ids ), $allocate_count
            )
        );
    }
}

WP_CLI::add_command( 'hr seed:assets', __NAMESPACE__ . '\\SeedAssets' );
