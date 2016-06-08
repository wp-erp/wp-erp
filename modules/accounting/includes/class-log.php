<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Accounting handler
 *
 * @package WP-ERP
 */
class Logger {

    use Hooker;

    //'Created vendor credit for vendorname with amount $5049'

    /**
     * Load autometically when class inistantiate
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {

        // Customer
        $this->action( 'erp_ac_after_new_customer', 'new_customer', 10, 2 );
        $this->action( 'erp_ac_before_update_customer', 'update_customer' );
        $this->action( 'erp_ac_new_transaction', 'new_transaction', 10, 3 );
        $this->action( 'erp_ac_new_journal', 'new_journal', 10, 3 );
        $this->action( 'erp_ac_new_account', 'new_account', 10, 2 );
        $this->action( 'erp_ac_before_update_account', 'update_account', 10, 3 );

    }
//new account has been created
    function new_account( $insert_id, $fields ) {

        $url     = admin_url( 'admin.php?page=erp-accounting-charts&action=view&id=' . $insert_id );
        $message = sprintf( '%1$s <a href="%2$s">%3$s</a> %4$s', __( 'New account', 'erp' ), $url, $fields['name'], __( 'has been created', 'erp' ) );

        erp_log()->add([
            'component'     => 'Accounting',
            'sub_component' => 'Chart of account',
            'old_value'     => '',
            'new_value'     => '',
            'message'       => $message,
            'changetype'    => 'add',
            'created_by'    => get_current_user_id()

        ]);
    }

    function update_account( $insert_id, $fields, $bank_details ) {
        if ( ! $insert_id ) return;

        $url     = admin_url( 'admin.php?page=erp-accounting-charts&action=view&id=' . $insert_id );
        $message = sprintf( '%1$s <a href="%2$s">%3$s</a> %4$s', __( 'New account', 'erp' ), $url, $fields['name'], __( 'has been created', 'erp' ) );

        $bank            = erp_ac_get_chart($insert_id)->toArray();
        $sub_bnk_detail  = [];
        $fild_bnk_detail = [];

        unset( $bank['parent'], $bank['type_id'], $bank['currency'], $bank['tax'], $bank['cash_account'] );
        unset( $bank['reconcile'], $bank['system'], $fields['type_id'] );

        if ( isset( $bank['bank_details'] ) && $bank['bank_details'] ) {
            $sub_bnk_detail = $bank['bank_details'];
            unset( $bank['bank_details'], $sub_bnk_detail['id'], $sub_bnk_detail['ledger_id'] );
        }

        if ( $bank_details ) {
            unset( $bank_details['id'], $bank_details['ledger_id'] );
        }

        $field_status = $fields['active'] ? __( 'Active', 'erp' ) : __( 'Inactive', 'erp' );
        $db_bank_status = $bank['active'] ? __( 'Active', 'erp' ) : __( 'Inactive', 'erp' );

        unset( $fields['active'], $bank['active'] );

        $fields['status'] = $field_status;
        $bank['status']   =  $db_bank_status;

        $sub_bnk_detail['account_number'] = isset( $sub_bnk_detail['account_number'] ) ? $sub_bnk_detail['account_number'] : '';
        $sub_bnk_detail['bank_name']      = isset( $sub_bnk_detail['bank_name'] ) ? $sub_bnk_detail['bank_name'] : '';

        $fields_merge  = array_merge( $fields, $bank_details );
        $sub_bnk_merge = array_merge( $bank, $sub_bnk_detail );

        $array_dif = $this->get_array_diff( $fields_merge, $sub_bnk_merge );

        erp_log()->add([
            'component'     => 'Accounting',
            'sub_component' => 'Chart of account',
            'changetype'    => 'edit',
            'old_value'     => $array_dif['old_val'],
            'new_value'     => $array_dif['new_val'],
            'message'       => $message,
            'created_by'    => get_current_user_id()
        ]);

    }

    function new_journal( $transaction_id, $args, $post ) {
        $url = admin_url( 'admin.php?page=erp-accounting-journal&action=view&id=' . $transaction_id );
        $message = sprintf( '%1$s <a href="%2$s">%3$s</a> %4$s %5$s',
            __( 'Created', 'erp' ),
            $url,
            __( 'journal', 'erp' ),
            __( 'with amount', 'erp' ),
            erp_ac_get_price( $args['trans_total'] )
        );

        erp_log()->add([
            'component'     => 'Accounting',
            'sub_component' => __( 'journal', 'erp' ),
            'old_value'     => '',
            'new_value'     => '',
            'message'       => $message,
            'changetype'    => 'add',
            'created_by'    => get_current_user_id()

        ]);
    }

    function new_transaction( $transaction_id, $args, $items ) {

        $people         = erp_get_people( $args['user_id'] );
        if ( is_wp_error( $people ) ) {
            $name = '';
        } else {
            $people = $people ? $people : get_user_by( 'id', $args['user_id'] );
            $name   = isset( $people->display_name ) ? $people->display_name : $people->first_name . ' ' . $people->last_name;
        }

        $page           = $args['type'] == 'sales' ? 'erp-accounting-customers' : 'erp-accounting-vendors';
        $component_page = $args['type'] == 'sales' ? 'erp-accounting-sales' : 'erp-accounting-expense';
        $user_url       = admin_url( 'admin.php?page=' . $page . '&action=view&id=' . $args['user_id'] );
        $component_url  = admin_url( 'admin.php?page=' . $component_page . '&action=view&id=' . $transaction_id );

        if ( $args['form_type'] == 'payment_voucher' ) {
            $form_type = __( 'payment voucher', 'erp' );
        } else if ( $args['form_type'] == 'vendor_credit' ) {
            $form_type = __( 'vendor credit', 'erp' );
        } else {
            $form_type = $args['form_type'];
        }

        $message = sprintf( '%1$s <a href="%2$s">%3$s </a> %4$s %5$s <a href="%6$s">%7$s</a> %8$s %9$s',
            __( 'Created', 'erp' ),
            $component_url,
            $args['type'],
            $form_type,
            __( 'for', 'erp' ),
            $user_url,
            $name,
            __( 'with amount', 'erp' ),
            erp_ac_get_price( $args['trans_total'] )
        );

        erp_log()->add([
            'component'     => 'Accounting',
            'sub_component' => $args['type'] == 'sales' ? __( 'Sales', 'erp' ) : __( 'Expenses', 'erp' ),
            'old_value'     => '',
            'new_value'     => '',
            'message'       => $message,
            'changetype'    => 'add',
            'created_by'    => get_current_user_id()

        ]);
    }

    /**
     * Add log when new customer created
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     * @param  array $fields
     *
     * @return void
     */
    public function new_customer( $customer_id, $fields ) {

        $page      = $fields['type'] == 'vendor' ? 'erp-accounting-vendors' : 'erp-accounting-customers';
        $url       = sprintf( '<a href="%1$s"><strong>%2$s</strong></a>', admin_url( 'admin.php?page='. $page .'&action=view&id=' . $customer_id ), $fields['first_name'] . ' ' . $fields['last_name'] );
        $component = $fields['type'] == 'vendor' ? __( 'vendor', 'erp' ) : __( 'customer', 'erp' );

        erp_log()->add([
            'component'     => 'Accounting',
            'sub_component' => $fields['type'] == 'vendor' ? __( 'Vendor', 'erp' ) : __( 'Customer', 'erp' ),
            'old_value'     => '',
            'new_value'     => '',
            'message'       => esc_html( $url )   .' '. $component . __( ' has been created', 'erp' ),
            'changetype'    => 'add',
            'created_by'    => get_current_user_id()

        ]);
    }

    /**
     * Add log when update customer
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_customer( $fields ) {
        $page        = $fields['type'] == 'vendor' ? 'erp-accounting-vendors' : 'erp-accounting-customers';
        $customer_id = isset( $fields['id'] ) ? intval( $fields['id'] ) : 0;
        $customer    = (array) erp_get_people( $customer_id );
        $component   = $fields['type'] == 'vendor' ? __( 'vendor', 'erp' ) : __( 'customer', 'erp' );

        if ( $customer ) {
            unset( $customer['created_at'], $customer['updated_at'] );
        }

        if ( in_array( $fields['type'], $customer['types'] ) ) {
            $customer['type'] = $fields['type'];
        } else {
            $customer['type'] = '';
        }

        unset( $customer['types'] );

        $changes = $this->get_array_diff( $fields, $customer );

        $url = sprintf( '<a href="%1$s"><strong>%2$s</strong></a>', admin_url( 'admin.php?page='. $page .'&action=view&id=' . $fields['id'] ), $fields['first_name'] . ' ' . $fields['last_name'] );

        erp_log()->add([
            'component'     => 'Accounting',
            'sub_component' => $fields['type'] == 'vendor' ? __( 'Vendor', 'erp' ) : __( 'Customer', 'erp' ),
            'changetype'    => 'edit',
            'old_value'     => $changes['old_val'],
            'new_value'     => $changes['new_val'],
            'message'       => esc_html( $url ) .' '. $component  . __( ' has been updated', 'erp' ),
            'created_by'    => get_current_user_id()
        ]);
    }

    /**
     * Get different array from two array
     *
     * @since 0.1
     *
     * @param  array $new_data
     * @param  array $old_data
     *
     * @return array
     */
    public function get_array_diff( $new_data, $old_data, $is_seriazie = false ) {

        $old_value = $new_value = [];
        $changes_key = array_keys( array_diff_assoc( $new_data, $old_data ) );

        foreach ( $changes_key as $key => $change_field_key ) {
            $old_value[$change_field_key] = $old_data[$change_field_key];
            $new_value[$change_field_key] = $new_data[$change_field_key];
        }

        if ( ! $is_seriazie ) {
            return [
                'new_val' => $new_value ? base64_encode( maybe_serialize( $new_value ) ) : '',
                'old_val' => $old_value ? base64_encode( maybe_serialize( $old_value ) ) : ''
            ];
        } else {
            return [
                'new_val' => $new_value,
                'old_val' => $old_value
            ];
        }
    }



    /**
     * Add log when department deleted
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     *
     * @return void
     */
    public function delete_department( $dept_id ) {

        if ( ! $dept_id ) {
            return;
        }

        $department = new \WeDevs\ERP\HRM\Department( intval( $dept_id ) );

        erp_log()->add([
            'sub_component' => 'department',
            'message'       => sprintf( '<strong>%s</strong> department has been deleted', $department->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when udpate department
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_department( $dept_id, $fields ) {

        if ( ! $dept_id ) {
            return;
        }

        $old_department = \WeDevs\ERP\HRM\Models\Department::find( $dept_id )->toArray();
        unset( $old_department['created_at'], $old_department['updated_at'] );

        $changes = $this->get_array_diff( $fields, $old_department, true );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = false;
        } else {
            array_walk ( $changes, function ( &$key ) {
                if ( isset( $key['lead'] ) ) {
                    if( $key['lead'] ) {
                        $employee = new \WeDevs\ERP\HRM\Employee( intval( $key['lead'] ) );
                        $key['department_lead'] = $employee->get_full_name();
                    } else {
                        $key['department_lead'] = 'No deparment leader';
                    }
                    unset( $key['lead'] );
                }

                if ( isset( $key['parent'] ) ) {
                    if( $key['parent'] ) {
                        $department = new \WeDevs\ERP\HRM\Department( intval( $key['parent'] ) );
                        $key['parent_department'] = $department->title;
                    } else {
                        $key['parent_department'] = 'No Parent Department';
                    }
                    unset( $key['parent'] );
                }
            } );

            $message = sprintf( '<strong>%s</strong> department has been edited', $old_department['title'] );
        }

        if ( $message ) {
            erp_log()->add([
                'sub_component' => 'department',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_val'] ? base64_encode( maybe_serialize( $changes['old_val'] ) ) : '',
                'new_value'     => $changes['new_val'] ? base64_encode( maybe_serialize( $changes['new_val'] ) ) : ''
            ]);
        }

    }

    /**
     * Add log when new designation created
     *
     * @since 0.1
     *
     * @param  integer $desig_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_designation( $desig_id, $fields ) {
        erp_log()->add([
            'sub_component' => 'designation',
            'message'       => sprintf( '<strong>%s</strong> designation has been created', $fields['title'] ),
            'created_by'    => get_current_user_id()
        ]);
    }

    /**
     * Add log when department deleted
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     *
     * @return void
     */
    public function delete_designation( $desig ) {
        if ( ! $desig ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'designation',
            'message'       => sprintf( '<strong>%s</strong> designation has been deleted', $desig->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when designation updated
     *
     * @since 0.1
     *
     * @param  integer $desig_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_designation( $desig_id, $fields ) {
        if ( ! $desig_id ) {
            return;
        }

        $old_desig = \WeDevs\ERP\HRM\Models\Designation::find( $desig_id )->toArray();
        unset( $old_desig['created_at'], $old_desig['updated_at'] );

        $changes = $this->get_array_diff( $fields, $old_desig );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = false;
        } else {
            $message = sprintf( '<strong>%s</strong> designation has been edited', $old_desig['title'] );
        }

        if ( $message ) {
            erp_log()->add([
                'sub_component' => 'designation',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_val'],
                'new_value'     => $changes['new_val']
            ]);
        }
    }

    /**
     * Logging for creating policy
     *
     * @since 0.1
     *
     * @param  integer $policy_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_policy( $policy_id, $fields ) {

        if ( ! $policy_id ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( '<strong>%s</strong> policy has been created', $fields['name'] ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ]);
    }

    /**
     * Adding log when policy deleted
     *
     * @since 0.1
     *
     * @param  integer $policy_id
     *
     * @return void
     */
    public function delete_policy( $policy_id ) {

        if ( ! $policy_id ) {
            return;
        }

        $policy = \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_id );

        if ( !$policy ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( '<strong>%s</strong> policy has been deleted', $policy->name ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when udpate policy
     *
     * @since 0.1
     *
     * @param  integer $policy_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_policy( $policy_id, $fields ) {

        if ( ! $policy_id ) {
            return;
        }

        $old_policy = \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_id )->toArray();
        unset( $old_policy['created_at'], $old_policy['updated_at'], $fields['instant_apply'] ) ;

        $old_policy['effective_date'] = erp_format_date( $old_policy['effective_date'], 'Y-m-d' );
        $fields['effective_date']     = erp_format_date( $fields['effective_date'], 'Y-m-d' );

        if ( isset( $fields['activate'] ) && $fields['activate'] == 1 ) {
            unset( $fields['execute_day'], $old_policy['execute_day'] );
        }

        $changes = $this->get_array_diff( $fields, $old_policy, true );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = false;
        } else {
            array_walk ( $changes, function ( &$key ) {

                if ( isset( $key['color'] ) ) {
                    $key['calender_color'] = sprintf( '<div style="width:60px; height:20px; background-color:%s"></div>', $key['color'] );
                    unset( $key['color'] );
                }

                if ( isset( $key['department'] ) ) {
                    if ( $key['department'] == '-1' ) {
                        $key['department'] = __( 'All Department', 'erp' );
                    } else {
                        $department = new \WeDevs\ERP\HRM\Department( intval( $key['department'] ) );
                        $key['department'] = $department->title;
                    }
                }

                if ( isset( $key['designation'] ) ) {
                    if ( $key['designation'] == '-1' ) {
                        $key['designation'] = __( 'All Designation', 'erp' );
                    } else {
                        $designation = new \WeDevs\ERP\HRM\Designation( intval( $key['designation'] ) );
                        $key['designation'] = $designation->title;
                    }
                }

                if ( isset( $key['location'] ) ) {
                    if ( $key['location'] == '-1' ) {
                        $key['location'] = __( 'All Location', 'erp' );
                    } else {
                        $location = erp_company_get_location_dropdown_raw();
                        $key['location'] = $location[$key['location']];
                    }
                }

                if ( isset( $key['gender'] ) ) {
                    $gender = erp_hr_get_genders( __( 'All', 'erp' ) );
                    $key['gender'] = $gender[$key['gender']];
                }

                if ( isset( $key['marital'] ) ) {
                    $marital = erp_hr_get_marital_statuses( __( 'All', 'erp' ) );
                    $key['marital'] = $marital[$key['marital']];
                }

                if ( isset( $key['activate'] ) ) {
                    $activate = array( '1' => __( 'Immediately', 'accounting'), '2' => __('After X Days', 'accounting'), '3' => __( 'Manually', 'accounting') );

                    if ( $key['activate'] == 2 ) {
                        $key['activation']   = str_replace( 'X', $key['execute_day'], $activate[$key['activate']] );
                    } else {
                        $key['activation'] = $activate[$key['activate']];
                    }

                    unset( $key['activate'] );
                    unset( $key['execute_day'] );
                }

                if ( isset( $key['effective_date'] ) ) {
                    $key['policy_effective_date'] = erp_format_date( $key['effective_date'] );
                    unset( $key['effective_date'] );
                }

            } );

            $message = sprintf( '<strong>%s</strong> policy has been edited', $old_policy['name'] );
        }

        if ( $message ) {
            erp_log()->add([
                'sub_component' => 'leave',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_val'] ? base64_encode( maybe_serialize( $changes['old_val'] ) ) : '',
                'new_value'     => $changes['new_val'] ? base64_encode( maybe_serialize( $changes['new_val'] ) ) : ''
            ]);
        }
    }

    /**
     * Add log when someone take leave
     *
     * @since 0.1
     *
     * @param  integer $request_id
     * @param  array $request
     * @param  array $leaves
     *
     * @return void
     */
    public function create_leave_request( $request_id, $request, $leaves ) {

        if ( ! $request_id ) {
            return;
        }

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $request['user_id'] ) );

        $message = sprintf( '<strong>%s</strong> take leave from <strong>%s</strong> to <strong>%s</strong> about <strong>%d</strong> days',
            $employee->get_full_name(),
            erp_format_date( $request['start_date'] ),
            erp_format_date( $request['end_date'] ),
            $request['days']
        );

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ]);
    }

    /**
     * Add log when entitlement created
     *
     * @since 0.1
     *
     * @param  integer $entitlement_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_entitlement( $entitlement_id, $fields ) {

        if ( ! $entitlement_id ) {
            return;
        }

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $fields['user_id'] ) );
        $message  = sprintf( '%s <strong>%s</strong>', __( 'A new entitlement has been created for'), $employee->get_full_name() );

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ]);
    }

    /**
     * Create hoiliday log
     *
     * @since 0.1
     *
     * @param  integer $holiday_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_holiday( $holiday_id, $fields ) {

        if ( ! $holiday_id ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( 'A new holiday named <strong>%s</strong> has been created', $fields['title'] ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ]);
    }

    /**
     * Delete holiday log insert
     *
     * @since 0.1
     *
     * @param  integer $holiday_id
     *
     * @return void
     */
    public function delete_holiday( $holiday_id ) {

        if ( ! $holiday_id ) {
            return;
        }

        $holiday = \WeDevs\ERP\HRM\Models\Leave_Holiday::find( $holiday_id );

        if ( !$holiday ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( '<strong>%s</strong> holiday has been deleted', $holiday->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when edit holiday
     *
     * @since 0.1
     *
     * @param  integer $holiday_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_holiday( $holiday_id, $fields ) {

        if ( ! $holiday_id ) {
            return;
        }

        $old_holiday = \WeDevs\ERP\HRM\Models\Leave_Holiday::find( $holiday_id )->toArray();
        unset( $old_holiday['created_at'], $old_holiday['updated_at'] );

        $old_holiday['start'] = erp_format_date( $old_holiday['start'], 'Y-m-d' );
        $old_holiday['end']   = erp_format_date( $old_holiday['end'], 'Y-m-d' );

        $fields['start'] = erp_format_date( $fields['start'], 'Y-m-d' );
        $fields['end']   = erp_format_date( $fields['end'], 'Y-m-d' );

        $changes = $this->get_array_diff( $fields, $old_holiday, true );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = false;
        } else {
            array_walk ( $changes, function ( &$key ) {
                if ( isset( $key['start'] ) ) {
                    $key['start_date'] = erp_format_date( $key['start'] );
                    unset( $key['start'] );
                }

                if ( isset( $key['end'] ) ) {
                    $key['end_date'] = erp_format_date( $key['end'] );
                    unset( $key['end'] );
                }
            } );
            $message = sprintf( '<strong>%s</strong> holiday has been edited', $old_holiday['title'] );
        }

        if ( $message ) {
            erp_log()->add([
                'sub_component' => 'leave',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_val'] ? base64_encode( maybe_serialize( $changes['old_val'] ) ) : '',
                'new_value'     => $changes['new_val'] ? base64_encode( maybe_serialize( $changes['new_val'] ) ) : ''
            ]);
        }
    }

    /**
     * Add log when announcement create or edit
     *
     * @since 0.1
     *
     * @param  string $new_status
     * @param  string $old_status
     * @param  object $post
     *
     * @return void
     */
    public function announcment_log( $new_status, $old_status, $post ) {
        if ( 'erp_hr_announcement' != $post->post_type ) {
            return;
        }

        if ( 'publish' !== $new_status ) {
            return;
        }

        $overview = add_query_arg( array( 'page' => 'erp-hr' ), admin_url('admin.php') );

        if ( 'publish' === $old_status ) {
            $message     = sprintf( "<strong>%s</strong> announcement has been edited", $post->post_title );
            $change_type = 'edit';
        } else {
            $message     = sprintf( "<strong>%s</strong> announcement has been created", $post->post_title );
            $change_type = 'add';
        }

        erp_log()->add([
            'sub_component' => 'announcement',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => $change_type,
        ]);

    }

}