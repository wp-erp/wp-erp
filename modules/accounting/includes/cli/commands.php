<?php
namespace WeDevs\ERP\Accounting\CLI;

/**
 * Accounting CLI class
 */
class Commands extends \WP_CLI_Command {

    function new_customers() {
        $results = [];

        $fake_customers = [
            [
                'first_name'  => 'Beverly',
                'last_name'   => 'Jefferson',
                'email'       => 'InezBSimpson@inbound.plus',
                'company'     => 'Payless Cashways',
                'type'        => 'customer',
                'street_1'    => '9880 Oleander Lane',
                'city'        => 'Dormansville',
                'state'       => 'NY',
                'phone'       => '(765) 629-3533'
            ],
            [
                'first_name'  => 'Jorden',
                'last_name'   => 'Stevenson',
                'email'       => 'EugeneMMatthews@inbound.plus',
                'company'     => 'The Original House of Pies',
                'type'        => 'customer',
                'street_1'    => '5853 120th Drive',
                'city'        => 'Foster',
                'state'       => 'MO',
                'phone'       => '(660) 619-4971'
            ],
            [
                'first_name'  => 'Christian',
                'last_name'   => 'Hale',
                'email'       => 'WilliamCRobertson@inbound.plus',
                'company'     => 'Back To Basics Chiropractic Clinic',
                'type'        => 'customer',
                'street_1'    => '9407 Ellis Drive',
                'city'        => 'Sautee Nacoochee',
                'state'       => 'GA',
                'phone'       => '(706) 673-9906'
            ]
        ];

        foreach ( $fake_customers as $customer ) { 
            $results[] = erp_insert_people( $customer );
        }

        return $results;
    }

    function new_vendors() {
        $results = [];

        $fake_vendors = [
            [
                'first_name'  => 'Mohammed',
                'last_name'   => 'Bridges',
                'email'       => 'DeborahRIverson@inbound.plus',
                'company'     => 'Endicott Johnson',
                'type'        => 'vendor',
                'street_1'    => '3420 Beaubien Court',
                'city'        => 'Hallandale',
                'state'       => 'FL',
                'phone'       => '(954) 201-6651'
            ],
            [
                'first_name'  => 'Ezequiel',
                'last_name'   => 'Rutledge',
                'email'       => 'LindaJPinkston@inbound.plus',
                'company'     => 'Country Club Markets',
                'type'        => 'vendor',
                'street_1'    => '8106 Congress Road',
                'city'        => 'Providence',
                'state'       => 'RI',
                'phone'       => '(401) 361-4590'
            ],
            [
                'first_name'  => 'Brad',
                'last_name'   => 'Parsons',
                'email'       => 'RaymondAReynolds@inbound.plus',
                'company'     => 'Atlas Architectural Designs',
                'type'        => 'vendor',
                'street_1'    => '1920 Dearborn Drive',
                'city'        => 'Linwood',
                'state'       => 'NY',
                'phone'       => '(585) 238-7943'
            ]
        ];

        foreach ( $fake_vendors as $vendor ) { 
            $results[] = erp_insert_people( $vendor );
        }

        return $results;
    }

    public function delete() {
        global $wpdb;
        // truncate table
        $tables = ['erp_ac_transactions', 'erp_ac_transaction_items', 'erp_ac_journals'];
        foreach ($tables as $table) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table);
        }
        \WP_CLI::success( "Table deleted successfully!" );
    }

    /**
     * Seed the database
     */
    public function seed( $args ) {
        global $wpdb, $current_user;
    
        $supper_admin_email = get_option( 'admin_email' );
        $current_user       = get_user_by( 'email', $supper_admin_email );

        // truncate table
        $tables = ['erp_ac_transactions', 'erp_ac_transaction_items', 'erp_ac_journals'];
        foreach ($tables as $table) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table);
        }

        $args         = array( 'type'   => 'customer' );
        $customers    = erp_get_peoples( $args );
        $customers_id = wp_list_pluck( $customers, 'id' );
        
        if ( ! count( $customers_id ) ) {
            $customers_id = $this->new_customers();
        } 

        // insert some sales data
        $data_count     = 10;

        $sales_type     = ['invoice', 'payment'];
        $sales_ledger   = [53, 54, 55];
        $expense_type   = ['payment_voucher', 'vendor_credit'];
        $bank_accounts  = [7, 62];


        for ($i = 0; $i < $data_count; $i++) {
            $date_due       = date( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
            $date_future    = date( 'Y-m-d', strtotime( '+' . $i + 7 . ' days' ) );
            $date           = [$date_future, $date_due];

            $form_type   = $sales_type[ array_rand( $sales_type ) ];
            $trans_total = rand( 100, 20000 );
            $user_id     = $customers_id[ array_rand( $customers_id ) ];
            $date        = $date[ array_rand( $date ) ];

            $sales = [
                'id'              => '',
                'type'            => 'sales',
                'form_type'       => $form_type,
                'account_id'      => ( $form_type == 'invoice' ) ? 1 : $bank_accounts[ array_rand( $bank_accounts ) ],
                'status'          => 'draft', //( $form_type == 'invoice' ) ? 'awaiting_payment' : 'closed',
                'user_id'         => $user_id,
                'billing_address' => 'Dhanmondi, Dhaka',
                'ref'             => '',
                'issue_date'      => date( 'Y-m-d', strtotime( '-' . $i . ' days' ) ),
                'due_date'        => ( $form_type == 'invoice' ) ? $date : null,
                'summary'         => '',
                'total'           => $trans_total,
                'trans_total'     => $trans_total,
                'sub_total'       => $trans_total,
                'files'           => '',
                'currency'        => erp_ac_get_currency(),
                'created_by'      => 1,
                'created_at'      => current_time( 'mysql' ),
                'partial_id'      => [],
                'items_id'        => [$i => ''],
                'journals_id'     => [$i => ''],
                'line_total'      => [$trans_total]
            ];

            $sales_item = [
                [
                    'item_id'     => '',
                    'journal_id'  => '',
                    'account_id'  => $sales_ledger[ array_rand( $sales_ledger ) ],
                    'description' => 'Some random description',
                    'qty'         => 1,
                    'unit_price'  => $trans_total,
                    'discount'    => 0,
                    'line_total'  => $trans_total,
                    'tax'         => '-1',
                    'tax_rate'    => '0.00',
                    'tax_journal' => 0
                ]
            ];

            erp_ac_insert_transaction( $sales, $sales_item );
        }

        $args       = array( 'type' => 'vendor' );
        $vendors    = erp_get_peoples( $args );
        $vendors_id = wp_list_pluck( $vendors, 'id' );
        
        if ( ! count( $vendors_id ) ) {
            $vendors_id = $this->new_vendors();
        } 

        // insert some expense data
        for ($i = 0; $i < $data_count; $i++) {
            $form_type   = $expense_type[ array_rand( $expense_type ) ];
            $trans_total = rand( 100, 20000 );
            $user_id     = $vendors_id[ array_rand( $vendors_id ) ];

            erp_ac_insert_transaction( [
                'type'            => 'expense',
                'form_type'       => $form_type,
                'account_id'      => ( $form_type == 'vendor_credit' ) ? 8 : $bank_accounts[ array_rand( $bank_accounts ) ],
                'status'          => 'draft', //'closed',
                'user_id'         => $user_id,
                'billing_address' => 'Dhanmondi, Dhaka',
                'ref'             => '',
                'issue_date'      => date( 'Y-m-d', strtotime( '-' . $i . ' days' ) ),
                'due_date'        => ( $form_type == 'vendor_credit' ) ? date( 'Y-m-d', strtotime( '+' . $i + 7 . ' days' ) ) : null,
                'summary'         => '',
                'total'           => $trans_total,
                'trans_total'     => $trans_total,
                'files'           => '',
                'currency'        => '',
                'partial_id'      => [],
                'created_by'      => 1,
                'created_at'      => current_time( 'mysql' )
            ], [
                [
                    'account_id'  => rand( 24, 49 ),
                    'description' => 'Some random description',
                    'qty'         => 1,
                    'unit_price'  => $trans_total,
                    'discount'    => 0,
                    'line_total'  => $trans_total,
                ]
            ] );
        }

        \WP_CLI::success( "Database has been seeded!" );
    }
}

\WP_CLI::add_command( 'accounting', 'WeDevs\ERP\Accounting\CLI\Commands' );
