<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;

class SeedCompanies extends AbstractCrmSeeder {

    /**
     * Generate CRM companies.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of companies to create.
     * ---
     * default: 15
     * ---
     *
     * ## EXAMPLES
     *
     *     wp crm seed:companies
     *     wp crm seed:companies --count=20
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        $count     = (int) ( $assoc_args['count'] ?? 15 );
        $companies = CrmDataProvider::companies();
        $cities    = CrmDataProvider::cities();
        $streets   = CrmDataProvider::streets();

        $progress    = $this->progress( 'Creating companies', $count );
        $created_ids = [];
        $fail_count  = 0;

        for ( $i = 0; $i < $count; $i++ ) {
            $template     = $companies[ $i % count( $companies ) ];
            $cycle        = (int) floor( $i / count( $companies ) );
            $company_data = [
                'name'    => $cycle > 0 ? $template['name'] . ' ' . ( $cycle + 1 ) : $template['name'],
                'website' => $template['website'],
            ];
            $city_data    = $this->random_element( $cities );

            // Generate a unique email for the company.
            $email_domain = str_replace( [ 'https://', 'http://', 'www.' ], '', $company_data['website'] );
            $email_base   = str_replace( '.example.com', '.test', $email_domain );
            $email        = $cycle > 0 ? 'info' . ( $cycle + 1 ) . '@' . $email_base : 'info@' . $email_base;

            $data = [
                'type'       => 'company',
                'company'    => $company_data['name'],
                'email'      => $email,
                'phone'      => sprintf( '(%03d) %03d-%04d', mt_rand( 200, 999 ), mt_rand( 200, 999 ), mt_rand( 1000, 9999 ) ),
                'website'    => $company_data['website'],
                'street_1'   => $this->random_element( $streets ),
                'city'       => $city_data['city'],
                'state'      => $city_data['state'],
                'postal_code'=> $city_data['postal'],
                'country'    => $city_data['country'],
                'life_stage' => CrmDataProvider::weighted_life_stage(),
                'source'     => $this->random_element( CrmDataProvider::sources() ),
                'contact_owner' => 1,
            ];

            $result = erp_insert_people( $data );

            if ( is_wp_error( $result ) ) {
                WP_CLI::warning( "Failed to create company '{$company_data['name']}': " . $result->get_error_message() );
                $fail_count++;
            } else {
                $created_ids[] = $result;

                // Add industry meta.
                $industries = [ 'Technology', 'Healthcare', 'Finance', 'Retail', 'Manufacturing', 'Education', 'Media', 'Consulting', 'Logistics' ];
                erp_people_update_meta( $result, 'industry', $this->random_element( $industries ) );

                // Add employee count meta.
                $employee_counts = [ '1-10', '11-50', '51-200', '201-500', '501-1000', '1000+' ];
                erp_people_update_meta( $result, 'employee_count', $this->random_element( $employee_counts ) );
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'company_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d companies (%d failed).', count( $created_ids ), $fail_count ) );
    }
}

WP_CLI::add_command( 'crm seed:companies', __NAMESPACE__ . '\\SeedCompanies' );
