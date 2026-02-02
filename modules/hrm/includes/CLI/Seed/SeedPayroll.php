<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedPayroll extends AbstractSeeder {

    /**
     * Generate payroll data - pay calendar, payruns, and disburse salary (Pro feature).
     *
     * ## OPTIONS
     *
     * [--months=<months>]
     * : Number of past months to generate payroll for.
     * ---
     * default: 6
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:payroll
     *     wp hr seed:payroll --months=12
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        if ( ! $this->table_exists( 'erp_hr_payroll_pay_calendar' ) ) {
            WP_CLI::warning( 'Payroll module not active (table erp_hr_payroll_pay_calendar not found). Skipping.' );
            return;
        }

        global $wpdb;

        $months       = (int) ( $assoc_args['months'] ?? 6 );
        $employee_ids = $this->get_employee_user_ids();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first.' );
        }

        $cal_table       = $wpdb->prefix . 'erp_hr_payroll_pay_calendar';
        $settings_table  = $wpdb->prefix . 'erp_hr_payroll_calendar_type_settings';
        $cal_emp_table   = $wpdb->prefix . 'erp_hr_payroll_pay_calendar_employee';
        $payitem_table   = $wpdb->prefix . 'erp_hr_payroll_payitem';
        $fixed_table     = $wpdb->prefix . 'erp_hr_payroll_fixed_payment';
        $payrun_table    = $wpdb->prefix . 'erp_hr_payroll_payrun';
        $detail_table    = $wpdb->prefix . 'erp_hr_payroll_payrun_detail';

        // 1. Create pay calendar.
        WP_CLI::log( 'Creating pay calendar...' );

        $existing_cal = $wpdb->get_var(
            "SELECT id FROM {$cal_table} WHERE pay_calendar_name = 'Monthly Payroll' LIMIT 1"
        );

        if ( $existing_cal ) {
            $cal_id = $existing_cal;
        } else {
            $wpdb->insert(
                $cal_table,
                [
                    'pay_calendar_name' => 'Monthly Payroll',
                    'pay_calendar_type' => 'monthly',
                ],
                [ '%s', '%s' ]
            );
            $cal_id = $wpdb->insert_id;

            $wpdb->insert(
                $settings_table,
                [
                    'pay_calendar_id'    => $cal_id,
                    'pay_day'            => -1,
                    'custom_month_day'   => 0,
                    'pay_day_mode'       => 0,
                ],
                [ '%d', '%d', '%d', '%d' ]
            );
        }

        // 2. Assign employees to pay calendar.
        $progress = $this->progress( 'Assigning employees to pay calendar', count( $employee_ids ) );

        foreach ( $employee_ids as $emp_id ) {
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$cal_emp_table} WHERE pay_calendar_id = %d AND empid = %d",
                    $cal_id,
                    $emp_id
                )
            );

            if ( ! $exists ) {
                $wpdb->insert(
                    $cal_emp_table,
                    [
                        'pay_calendar_id' => $cal_id,
                        'empid'           => $emp_id,
                    ],
                    [ '%d', '%d' ]
                );
            }

            $progress->tick();
        }

        $progress->finish();

        // 3. Get or create pay items.
        $pay_items = $this->ensure_pay_items( $payitem_table );

        // 4. Create fixed payments for employees.
        WP_CLI::log( 'Creating fixed salary components...' );
        $progress = $this->progress( 'Setting up fixed payments', count( $employee_ids ) );

        foreach ( $employee_ids as $emp_id ) {
            $emp = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT pay_rate FROM {$wpdb->prefix}erp_hr_employees WHERE user_id = %d",
                    $emp_id
                )
            );

            $basic = $emp ? (float) $emp->pay_rate : 5000;

            // Basic Salary.
            $this->insert_fixed_payment( $fixed_table, $cal_id, $emp_id, $pay_items['Basic Salary'], $basic );

            // House Rent Allowance (30% of basic).
            if ( isset( $pay_items['House Rent Allowance'] ) ) {
                $this->insert_fixed_payment( $fixed_table, $cal_id, $emp_id, $pay_items['House Rent Allowance'], round( $basic * 0.30, 2 ) );
            }

            // Transport Allowance (flat).
            if ( isset( $pay_items['Transport Allowance'] ) ) {
                $this->insert_fixed_payment( $fixed_table, $cal_id, $emp_id, $pay_items['Transport Allowance'], 200 );
            }

            // Provident Fund deduction (5% of basic).
            if ( isset( $pay_items['Provident Fund'] ) ) {
                $this->insert_fixed_payment( $fixed_table, $cal_id, $emp_id, $pay_items['Provident Fund'], round( $basic * 0.05, 2 ) );
            }

            $progress->tick();
        }

        $progress->finish();

        // 5. Create monthly payruns.
        WP_CLI::log( sprintf( 'Creating %d monthly payruns...', $months ) );
        $progress = $this->progress( 'Creating payruns', $months );

        for ( $m = $months; $m >= 1; $m-- ) {
            $from_date    = date( 'Y-m-01', strtotime( "-{$m} months" ) );
            $to_date      = date( 'Y-m-t', strtotime( "-{$m} months" ) );
            $payment_date = $to_date;

            // Check if payrun exists.
            $existing_pr = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$payrun_table} WHERE pay_cal_id = %d AND from_date = %s",
                    $cal_id,
                    $from_date
                )
            );

            if ( $existing_pr ) {
                $progress->tick();
                continue;
            }

            $wpdb->insert(
                $payrun_table,
                [
                    'pay_cal_id'     => $cal_id,
                    'payment_date'   => $payment_date,
                    'from_date'      => $from_date,
                    'to_date'        => $to_date,
                    'approve_status' => 1,
                ],
                [ '%d', '%s', '%s', '%s', '%d' ]
            );

            $payrun_id = $wpdb->insert_id;

            // Create payrun details for each employee.
            foreach ( $employee_ids as $emp_id ) {
                $emp = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT pay_rate FROM {$wpdb->prefix}erp_hr_employees WHERE user_id = %d",
                        $emp_id
                    )
                );

                $basic     = $emp ? (float) $emp->pay_rate : 5000;
                $hra       = round( $basic * 0.30, 2 );
                $transport = 200;
                $pf        = round( $basic * 0.05, 2 );

                $allowance  = $basic + $hra + $transport;
                $deduction  = $pf;
                $net_pay    = $allowance - $deduction;

                // Basic salary detail.
                $this->insert_payrun_detail( $detail_table, $payrun_id, $cal_id, $payment_date, $emp_id, $pay_items['Basic Salary'], $basic, 1, $allowance, $deduction );

                // HRA detail.
                if ( isset( $pay_items['House Rent Allowance'] ) ) {
                    $this->insert_payrun_detail( $detail_table, $payrun_id, $cal_id, $payment_date, $emp_id, $pay_items['House Rent Allowance'], $hra, 1, 0, 0 );
                }

                // Transport detail.
                if ( isset( $pay_items['Transport Allowance'] ) ) {
                    $this->insert_payrun_detail( $detail_table, $payrun_id, $cal_id, $payment_date, $emp_id, $pay_items['Transport Allowance'], $transport, 1, 0, 0 );
                }

                // PF deduction detail.
                if ( isset( $pay_items['Provident Fund'] ) ) {
                    $this->insert_payrun_detail( $detail_table, $payrun_id, $cal_id, $payment_date, $emp_id, $pay_items['Provident Fund'], $pf, 0, 0, 0 );
                }
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success(
            sprintf(
                'Payroll setup complete: 1 pay calendar, %d employees assigned, %d months of payruns created and disbursed.',
                count( $employee_ids ),
                $months
            )
        );
    }

    private function ensure_pay_items( $table ) {
        global $wpdb;

        $items = [
            'Basic Salary'          => 1,
            'House Rent Allowance'  => 1,
            'Transport Allowance'   => 1,
            'Medical Allowance'     => 1,
            'Provident Fund'        => 0,
            'Professional Tax'      => 0,
            'Income Tax'            => 2,
        ];

        $result = [];

        foreach ( $items as $name => $type ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE payitem = %s",
                    $name
                )
            );

            if ( $existing ) {
                $result[ $name ] = $existing;
            } else {
                $wpdb->insert(
                    $table,
                    [
                        'payitem'                => $name,
                        'pay_item_add_or_deduct' => $type,
                        'type'                   => 'payrun',
                    ],
                    [ '%s', '%d', '%s' ]
                );

                $result[ $name ] = $wpdb->insert_id;
            }
        }

        return $result;
    }

    private function insert_fixed_payment( $table, $cal_id, $emp_id, $payitem_id, $amount ) {
        global $wpdb;

        // Get pay_item_add_or_deduct from payitem table
        $payitem_table = str_replace( 'fixed_payment', 'payitem', $table );
        $add_or_deduct = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT pay_item_add_or_deduct FROM {$payitem_table} WHERE id = %d",
                $payitem_id
            )
        );

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table} WHERE empid = %d AND pay_item_id = %d",
                $emp_id,
                $payitem_id
            )
        );

        if ( $exists ) {
            return;
        }

        $wpdb->insert(
            $table,
            [
                'pay_item_id'            => $payitem_id,
                'pay_item_amount'        => $amount,
                'empid'                  => $emp_id,
                'pay_item_add_or_deduct' => $add_or_deduct,
            ],
            [ '%d', '%f', '%d', '%d' ]
        );
    }

    private function insert_payrun_detail( $table, $payrun_id, $cal_id, $payment_date, $emp_id, $payitem_id, $amount, $add_or_deduct, $allowance, $deduction ) {
        global $wpdb;

        $wpdb->insert(
            $table,
            [
                'payrun_id'              => $payrun_id,
                'pay_cal_id'             => $cal_id,
                'payment_date'           => $payment_date,
                'empid'                  => $emp_id,
                'pay_item_id'            => $payitem_id,
                'pay_item_amount'        => $amount,
                'pay_item_add_or_deduct' => $add_or_deduct,
                'allowance'              => $allowance,
                'deduction'              => $deduction,
                'approve_status'         => 1,
            ],
            [ '%d', '%d', '%s', '%d', '%d', '%f', '%d', '%f', '%f', '%d' ]
        );
    }
}

WP_CLI::add_command( 'hr seed:payroll', __NAMESPACE__ . '\\SeedPayroll' );
