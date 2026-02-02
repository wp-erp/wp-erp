<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;

class SeedDeals extends AbstractCrmSeeder {

    /**
     * Generate CRM deals and pipelines (Pro feature).
     *
     * ## OPTIONS
     *
     * [--deals=<count>]
     * : Number of deals to create.
     * ---
     * default: 30
     * ---
     *
     * ## EXAMPLES
     *
     *     wp crm seed:deals
     *     wp crm seed:deals --deals=50
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        // Check if deals table exists (Pro feature).
        if ( ! $this->table_exists( 'erp_crm_deals' ) ) {
            WP_CLI::warning( 'Deals feature not available (Pro feature). Skipping.' );
            return;
        }

        global $wpdb;

        $deal_count  = (int) ( $assoc_args['deals'] ?? 30 );
        $contact_ids = $this->get_contact_ids();
        $company_ids = $this->get_company_ids();

        if ( empty( $contact_ids ) && empty( $company_ids ) ) {
            WP_CLI::error( 'No contacts or companies found. Run seed:contacts or seed:companies first.' );
        }

        // Create pipelines and stages first.
        $pipeline_data = $this->create_pipelines();

        if ( empty( $pipeline_data ) ) {
            WP_CLI::error( 'Failed to create pipelines.' );
        }

        // Create lost reasons.
        $lost_reason_ids = $this->create_lost_reasons();

        // Create deals.
        $this->create_deals( $deal_count, $pipeline_data, $contact_ids, $company_ids, $lost_reason_ids );

        WP_CLI::success( 'Deal seeding complete.' );
    }

    /**
     * Create pipelines and stages.
     *
     * @return array Pipeline data with stage IDs.
     */
    private function create_pipelines() {
        global $wpdb;

        $pipelines     = CrmDataProvider::pipelines();
        $pipeline_data = [];

        $progress = $this->progress( 'Creating pipelines', count( $pipelines ) );

        foreach ( $pipelines as $pipeline ) {
            // Check if pipeline already exists.
            $existing_pipeline = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}erp_crm_deals_pipelines WHERE title = %s",
                    $pipeline['title']
                )
            );

            if ( $existing_pipeline ) {
                $pipeline_id = $existing_pipeline->id;
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'erp_crm_deals_pipelines',
                    [ 'title' => $pipeline['title'] ],
                    [ '%s' ]
                );
                $pipeline_id = $wpdb->insert_id;
            }

            $stage_ids = [];

            foreach ( $pipeline['stages'] as $stage ) {
                // Check if stage already exists.
                $existing_stage = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}erp_crm_deals_pipeline_stages WHERE pipeline_id = %d AND title = %s",
                        $pipeline_id,
                        $stage['title']
                    )
                );

                if ( $existing_stage ) {
                    $stage_ids[] = [
                        'id'          => $existing_stage->id,
                        'title'       => $stage['title'],
                        'probability' => $stage['probability'],
                        'life_stage'  => $stage['life_stage'],
                    ];
                } else {
                    $wpdb->insert(
                        $wpdb->prefix . 'erp_crm_deals_pipeline_stages',
                        [
                            'title'         => $stage['title'],
                            'pipeline_id'   => $pipeline_id,
                            'probability'   => $stage['probability'],
                            'is_rotting_on' => 0,
                            'rotting_after' => 0,
                            'life_stage'    => $stage['life_stage'],
                            'order'         => $stage['order'],
                        ],
                        [ '%s', '%d', '%f', '%d', '%d', '%s', '%d' ]
                    );

                    $stage_ids[] = [
                        'id'          => $wpdb->insert_id,
                        'title'       => $stage['title'],
                        'probability' => $stage['probability'],
                        'life_stage'  => $stage['life_stage'],
                    ];
                }
            }

            $pipeline_data[] = [
                'id'     => $pipeline_id,
                'title'  => $pipeline['title'],
                'stages' => $stage_ids,
            ];

            $progress->tick();
        }

        $progress->finish();

        // Store pipeline IDs.
        $this->store_ids( 'pipeline_ids', array_column( $pipeline_data, 'id' ) );

        WP_CLI::log( sprintf( '  Created %d pipelines with stages.', count( $pipeline_data ) ) );

        return $pipeline_data;
    }

    /**
     * Create lost deal reasons.
     *
     * @return array Lost reason IDs.
     */
    private function create_lost_reasons() {
        global $wpdb;

        // Check if lost reasons table exists.
        if ( ! $this->table_exists( 'erp_crm_deals_lost_reasons' ) ) {
            return [];
        }

        $reasons    = CrmDataProvider::lost_reasons();
        $reason_ids = [];

        foreach ( $reasons as $reason ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}erp_crm_deals_lost_reasons WHERE reason = %s",
                    $reason
                )
            );

            if ( $existing ) {
                $reason_ids[] = (int) $existing;
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'erp_crm_deals_lost_reasons',
                    [ 'reason' => $reason ],
                    [ '%s' ]
                );
                $reason_ids[] = $wpdb->insert_id;
            }
        }

        return $reason_ids;
    }

    /**
     * Create deals.
     *
     * @param int   $count
     * @param array $pipeline_data
     * @param array $contact_ids
     * @param array $company_ids
     * @param array $lost_reason_ids
     */
    private function create_deals( $count, $pipeline_data, $contact_ids, $company_ids, $lost_reason_ids ) {
        global $wpdb;

        $deal_titles = CrmDataProvider::deal_titles();
        $currencies  = CrmDataProvider::currencies();
        $notes       = CrmDataProvider::notes();

        $progress    = $this->progress( 'Creating deals', $count );
        $created_ids = [];
        $fail_count  = 0;

        for ( $i = 0; $i < $count; $i++ ) {
            // Select random pipeline and stage.
            $pipeline   = $this->random_element( $pipeline_data );
            $stage      = $this->random_element( $pipeline['stages'] );
            $is_won     = $stage['probability'] >= 100;
            $is_lost    = ! $is_won && mt_rand( 1, 100 ) <= 15; // 15% of non-won deals are lost.

            // Select contact and/or company.
            $contact_id = ! empty( $contact_ids ) ? $this->random_element( $contact_ids ) : null;
            $company_id = ! empty( $company_ids ) && mt_rand( 0, 1 ) ? $this->random_element( $company_ids ) : null;

            $created_at          = $this->random_datetime_between( date( 'Y-m-d', strtotime( '-2 years' ) ), date( 'Y-m-d' ) );
            $expected_close_date = date( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . mt_rand( 14, 90 ) . ' days' ) );
            $deal_value          = round( mt_rand( 1000, 100000 ) + ( mt_rand( 0, 99 ) / 100 ), 2 );

            $deal_data = [
                'title'               => $this->random_element( $deal_titles ) . ' #' . ( $i + 1 ),
                'stage_id'            => $stage['id'],
                'contact_id'          => $contact_id,
                'company_id'          => $company_id,
                'created_by'          => \get_current_user_id(),
                'owner_id'            => \get_current_user_id(),
                'value'               => $deal_value,
                'currency'            => $this->random_element( $currencies ),
                'expected_close_date' => $expected_close_date,
                'created_at'          => $created_at,
                'updated_at'          => $created_at,
            ];

            if ( $is_won ) {
                $deal_data['won_at'] = date( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . mt_rand( 7, 60 ) . ' days' ) );
            } elseif ( $is_lost && ! empty( $lost_reason_ids ) ) {
                $deal_data['lost_at']        = date( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . mt_rand( 7, 45 ) . ' days' ) );
                $deal_data['lost_reason_id'] = $this->random_element( $lost_reason_ids );
            }

            $result = $wpdb->insert(
                $wpdb->prefix . 'erp_crm_deals',
                $deal_data,
                [ '%s', '%d', '%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ]
            );

            if ( $result ) {
                $deal_id       = $wpdb->insert_id;
                $created_ids[] = $deal_id;

                // Add stage history.
                $this->add_stage_history( $deal_id, $stage['id'], $deal_value, $expected_close_date, $created_at );

                // Add random notes to some deals (60% chance).
                if ( mt_rand( 1, 100 ) <= 60 ) {
                    $this->add_deal_note( $deal_id, $notes, $created_at );
                }

                // Add random activities to some deals (40% chance).
                if ( mt_rand( 1, 100 ) <= 40 ) {
                    $this->add_deal_activity( $deal_id, $contact_id, $company_id, $created_at );
                }
            } else {
                $fail_count++;
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'deal_ids', $created_ids );

        WP_CLI::log( sprintf( '  Created %d deals (%d failed).', count( $created_ids ), $fail_count ) );
    }

    /**
     * Add stage history entry.
     *
     * @param int    $deal_id
     * @param int    $stage_id
     * @param float  $value
     * @param string $expected_close
     * @param string $created_at
     */
    private function add_stage_history( $deal_id, $stage_id, $value, $expected_close, $created_at ) {
        global $wpdb;

        if ( ! $this->table_exists( 'erp_crm_deals_stage_history' ) ) {
            return;
        }

        $wpdb->insert(
            $wpdb->prefix . 'erp_crm_deals_stage_history',
            [
                'deal_id'             => $deal_id,
                'stage_id'            => $stage_id,
                'in'                  => $created_at,
                'in_amount'           => $value,
                'expected_close_date' => $expected_close,
                'modified_by'         => 1,
            ],
            [ '%d', '%d', '%s', '%f', '%s', '%d' ]
        );
    }

    /**
     * Add a note to a deal.
     *
     * @param int    $deal_id
     * @param array  $notes
     * @param string $created_at
     */
    private function add_deal_note( $deal_id, $notes, $created_at ) {
        global $wpdb;

        if ( ! $this->table_exists( 'erp_crm_deals_notes' ) ) {
            return;
        }

        $wpdb->insert(
            $wpdb->prefix . 'erp_crm_deals_notes',
            [
                'deal_id'    => $deal_id,
                'note'       => $this->random_element( $notes ),
                'created_by' => 1,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ],
            [ '%d', '%s', '%d', '%s', '%s' ]
        );
    }

    /**
     * Add an activity to a deal.
     *
     * @param int      $deal_id
     * @param int|null $contact_id
     * @param int|null $company_id
     * @param string   $created_at
     */
    private function add_deal_activity( $deal_id, $contact_id, $company_id, $created_at ) {
        global $wpdb;

        if ( ! $this->table_exists( 'erp_crm_deals_activities' ) ) {
            return;
        }

        $types  = [ 'call', 'email', 'meeting', 'task' ];
        $titles = CrmDataProvider::meeting_subjects();

        $start = date( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . mt_rand( 1, 14 ) . ' days' ) );
        $end   = date( 'Y-m-d H:i:s', strtotime( $start . ' +' . mt_rand( 15, 120 ) . ' minutes' ) );

        $wpdb->insert(
            $wpdb->prefix . 'erp_crm_deals_activities',
            [
                'type'           => $this->random_element( $types ),
                'title'          => $this->random_element( $titles ),
                'deal_id'        => $deal_id,
                'contact_id'     => $contact_id,
                'company_id'     => $company_id,
                'created_by'     => 1,
                'assigned_to_id' => 1,
                'start'          => $start,
                'end'            => $end,
                'is_start_time_set' => 1,
                'created_at'     => $created_at,
            ],
            [ '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s' ]
        );
    }
}

WP_CLI::add_command( 'crm seed:deals', __NAMESPACE__ . '\\SeedDeals' );
