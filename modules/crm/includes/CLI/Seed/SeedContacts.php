<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;

class SeedContacts extends AbstractCrmSeeder {

    /**
     * Generate CRM contacts (leads, subscribers, opportunities, customers).
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of contacts to create.
     * ---
     * default: 50
     * ---
     *
     * ## EXAMPLES
     *
     *     wp crm seed:contacts
     *     wp crm seed:contacts --count=100
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        $count       = (int) ( $assoc_args['count'] ?? 50 );
        $first_names = CrmDataProvider::first_names();
        $last_names  = CrmDataProvider::last_names();
        $job_titles  = CrmDataProvider::job_titles();
        $cities      = CrmDataProvider::cities();
        $streets     = CrmDataProvider::streets();
        $sources     = CrmDataProvider::sources();
        $company_ids = $this->get_company_ids();
        $group_ids   = $this->get_contact_group_ids();

        $progress    = $this->progress( 'Creating contacts', $count );
        $created_ids = [];
        $fail_count  = 0;
        $used_emails = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $first_name = $this->random_element( $first_names );
            $last_name  = $this->random_element( $last_names );
            $city_data  = $this->random_element( $cities );

            // Generate unique email.
            $base_email = strtolower( $first_name . '.' . $last_name );
            $email      = $base_email . '@example.com';
            $counter    = 1;

            while ( in_array( $email, $used_emails, true ) ) {
                $email = $base_email . $counter . '@example.com';
                $counter++;
            }

            $used_emails[] = $email;

            $life_stage = CrmDataProvider::weighted_life_stage();

            $data = [
                'type'          => 'contact',
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'email'         => $email,
                'phone'         => sprintf( '(%03d) %03d-%04d', mt_rand( 200, 999 ), mt_rand( 200, 999 ), mt_rand( 1000, 9999 ) ),
                'mobile'        => sprintf( '(%03d) %03d-%04d', mt_rand( 200, 999 ), mt_rand( 200, 999 ), mt_rand( 1000, 9999 ) ),
                'street_1'      => $this->random_element( $streets ),
                'city'          => $city_data['city'],
                'state'         => $city_data['state'],
                'postal_code'   => $city_data['postal'],
                'country'       => $city_data['country'],
                'life_stage'    => $life_stage,
                'source'        => $this->random_element( $sources ),
                'contact_owner' => 1,
            ];

            $result = erp_insert_people( $data );

            if ( is_wp_error( $result ) ) {
                WP_CLI::warning( "Failed to create contact '{$first_name} {$last_name}': " . $result->get_error_message() );
                $fail_count++;
                $progress->tick();
                continue;
            }

            $contact_id    = $result;
            $created_ids[] = $contact_id;

            // Add job title meta.
            erp_people_update_meta( $contact_id, 'job_title', $this->random_element( $job_titles ) );

            // Add social profiles meta.
            $socials = [ 'facebook', 'twitter', 'linkedin' ];
            foreach ( $socials as $social ) {
                if ( mt_rand( 0, 1 ) ) {
                    $username = strtolower( $first_name . $last_name . mt_rand( 1, 999 ) );
                    erp_people_update_meta( $contact_id, $social, 'https://' . $social . '.com/' . $username );
                }
            }

            // Optionally associate with a company (50% chance if companies exist).
            if ( ! empty( $company_ids ) && mt_rand( 0, 1 ) ) {
                $company_id = $this->random_element( $company_ids );
                $this->associate_with_company( $contact_id, $company_id );
            }

            // Optionally subscribe to contact groups (40% chance per group).
            if ( ! empty( $group_ids ) ) {
                $subscribe_count = mt_rand( 0, min( 3, count( $group_ids ) ) );

                if ( $subscribe_count > 0 ) {
                    $random_groups = array_rand( array_flip( $group_ids ), $subscribe_count );

                    if ( ! is_array( $random_groups ) ) {
                        $random_groups = [ $random_groups ];
                    }

                    foreach ( $random_groups as $group_id ) {
                        $this->subscribe_to_group( $contact_id, $group_id );
                    }
                }
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'contact_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d contacts (%d failed).', count( $created_ids ), $fail_count ) );
    }

    /**
     * Associate a contact with a company.
     *
     * @param int $contact_id
     * @param int $company_id
     */
    private function associate_with_company( $contact_id, $company_id ) {
        global $wpdb;

        // Check if association already exists.
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_crm_customer_companies WHERE customer_id = %d AND company_id = %d",
                $contact_id,
                $company_id
            )
        );

        if ( ! $exists ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_crm_customer_companies',
                [
                    'customer_id' => $contact_id,
                    'company_id'  => $company_id,
                ],
                [ '%d', '%d' ]
            );
        }
    }

    /**
     * Subscribe a contact to a group.
     *
     * @param int $contact_id
     * @param int $group_id
     */
    private function subscribe_to_group( $contact_id, $group_id ) {
        global $wpdb;

        // Check if subscription already exists.
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_crm_contact_subscriber WHERE user_id = %d AND group_id = %d",
                $contact_id,
                $group_id
            )
        );

        if ( ! $exists ) {
            $statuses = [ 'subscribe', 'subscribe', 'subscribe', 'unconfirmed' ];

            $wpdb->insert(
                $wpdb->prefix . 'erp_crm_contact_subscriber',
                [
                    'user_id'      => $contact_id,
                    'group_id'     => $group_id,
                    'status'       => $this->random_element( $statuses ),
                    'subscribe_at' => current_time( 'mysql' ),
                    'hash'         => wp_generate_password( 20, false ),
                ],
                [ '%d', '%d', '%s', '%s', '%s' ]
            );
        }
    }
}

WP_CLI::add_command( 'crm seed:contacts', __NAMESPACE__ . '\\SeedContacts' );
