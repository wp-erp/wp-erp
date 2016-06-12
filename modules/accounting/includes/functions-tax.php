<?php
function erp_ac_tax_type() {
	return [ 0 => __( 'Single Tax', 'erp' ), 1 => __( 'Multi Tax', 'erp' ) ];
}

function erp_ac_new_tax( $postdata ) {

	global $wpdb;

    $args = array(
        'name'        => isset( $postdata['tax_name'] ) ? $postdata['tax_name'] : '',
        'tax_number'  => isset( $postdata['tax_number'] ) ? $postdata['tax_number'] : '',
        'is_compound' => isset( $postdata['compound'] ) ? $postdata['compound'] : '',
        'created_by'  => isset( $postdata['created_by'] ) ? $postdata['created_by'] : get_current_user_id()
    );

    $tax = new WeDevs\ERP\Accounting\Model\Tax();

    if ( $postdata['id'] ) {
        return $tax->find( $postdata['id'] )->update( $args );
    } else {
        return $tax->create( $args );
    }
}

function erp_ac_update_tax_items( $args, $tax_id = false ) {
    if ( ! intval( $tax_id ) ) {
        return false;
    }

    $is_compound = isset( $args['compound'] ) ? true : false;

    if ( ! $is_compound ) {
        $args['items_id'] = array( reset( $args['items_id'] ) );
    }

    $tax_items = new WeDevs\ERP\Accounting\Model\Tax_Items();

    if ( intval( $args['id'] ) ) {
        $get_deleted_items = $tax_items->select('id')->where( 'tax_id', '=', intval( $args['id'] ) )->get()->toArray();
        $get_deleted_items = wp_list_pluck( $get_deleted_items, 'id' );
        $insert_args       = array();
        $arg_items         = isset( $args['items_id'] ) && is_array( $args['items_id'] ) ? $args['items_id'] : array();

        foreach ( $arg_items as $key => $item_id ) {
            $insert_args = array(
                'tax_id'         => $tax_id,
                'component_name' => isset( $args['component_name'][$key] ) ? $args['component_name'][$key] : '',
                'agency_name'    => isset( $args['agency_name'][$key] ) ? $args['agency_name'][$key] : '',
                'tax_rate'       => isset( $args['tax_rate'][$key] ) ? $args['tax_rate'][$key] : '',
            );

            if ( $item_id ) {
                $tax_items->find( $item_id )->update( $insert_args );
            } else {
                $tax_items->create( $insert_args );
            }
        }

        $delete = array_diff( $get_deleted_items, $args['items_id'] );

        if ( is_array( $delete ) && count( $delete ) ) {
            erp_ac_remove_tax_items( $delete );
        }

    } else {
        $insert_args = array();
        $arg_items = isset( $args['items_id'] ) && is_array( $args['items_id'] ) ? $args['items_id'] : array();

        foreach ( $arg_items as $key => $component ) {
            $insert_args[] = array(
                'tax_id'         => $tax_id,
                'component_name' => isset( $args['component_name'][$key] ) ? $args['component_name'][$key] : '',
                'agency_name'    => isset( $args['agency_name'][$key] ) ? $args['agency_name'][$key] : '',
                'tax_rate'       => isset( $args['tax_rate'][$key] ) ? $args['tax_rate'][$key] : '',
            );
        }

        $tax_items->insert( $insert_args );
    }
}

function erp_ac_remove_tax_items( $items_id ) {
    $tax_items = new WeDevs\ERP\Accounting\Model\Tax_Items();

    if ( is_array( $items_id ) ) {
        $tax_items->destroy( $items_id );
        return true;
    }

    $tax_items->find($items_id)->delete();
}

function erp_ac_delete_tax( $tax_id ) {

    $ledgers       = WeDevs\ERP\Accounting\Model\Ledger::with( [ 'journals' ] )->WHERE( 'tax', '=', $tax_id )->get()->toArray();
    $record_exist = false;
    $ledgers_id   = [];

    foreach ( $ledgers as $ledger_attr ) {
        $ledgers_id[] = $ledger_attr['id'];
        if ( $ledger_attr['journals'] ) {
            $record_exist = true;
        }
    }

    if ( $record_exist ) {
        return new WP_Error( 'id_exist', __( 'The tax record can not be deleted as it contains one or more transactions.', 'erp' ) );
    }

    //Delete tax items
    $tax_items = WeDevs\ERP\Accounting\Model\Tax_Items::select('id')->where( 'tax_id', '=', $tax_id )->get()->toArray();
    $tax_items = wp_list_pluck( $tax_items, 'id' );

    if ( $tax_items ) {
        erp_ac_remove_tax_items( $tax_items );
    }

    //Delete tax ledger

    if ( $ledgers_id ) {
        WeDevs\ERP\Accounting\Model\Ledger::destroy( $ledgers_id );
    }

    //Delete tax
    WeDevs\ERP\Accounting\Model\Tax::find($tax_id)->delete();

    return true;
}

function erp_ac_get_tax_dropdown() {
    $taxs    = erp_ac_get_all_tax( [ 'number' => '-1' ] );
    $drpdown = [];

    foreach ($taxs as $tax ) {
        $drpdown[$tax->id] = $tax->name;
    }

    return $drpdown;
}

function erp_ac_get_tax_info() {
    $cache_key       = 'erp-ac-tax-info-' . md5( serialize( get_current_user_id() ) );
    $tax_rate_info   = wp_cache_get( $cache_key, 'erp' );

    if ( false === $tax_rate_info ) {
        $taxs      = erp_ac_get_all_tax( [ 'number' => '-1', 'join' => ['items'] ] );
        $tax_rate_info = [];

        foreach ($taxs as $tax ) {
            $tax_info = [];

            if ( $tax->items ) {
                $rate       = wp_list_pluck( $tax->items, 'tax_rate' );
                $tax_info = [ 'id' => $tax->id, 'name' => $tax->name, 'number' => $tax->tax_number, 'rate' =>  array_sum( $rate ) ];
            }

            $tax_rate_info[$tax->id] = $tax_info;
        }

        wp_cache_set( $cache_key, $tax_rate_info, 'erp' );
    }
    return $tax_rate_info;
}

function erp_ac_get_all_tax( $args = [] ) {
	global $wpdb;

    $defaults = array(
        'id'         => 0,
        'account'    => 0,
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'output_by'  => 'object'
    );

    $args            = wp_parse_args( $args, $defaults );
    $cache_key       = 'erp-ac-tax-all-' . md5( serialize( $args ) );
    $items           = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $tax = new WeDevs\ERP\Accounting\Model\Tax();
        $db  = new \WeDevs\ORM\Eloquent\Database();

        if ( isset( $args['select'] ) && count( $args['select'] ) ) {
            //demo [ '*', $db->raw( 'MONTHNAME( issue_date ) as month' ) ]
            $tax = $tax->select( $args['select'] );
        }

        if ( isset( $args['join'] ) && count( $args['join'] ) ) {

            $tax = $tax->with( $args['join'] );
        }

        if ( isset( $args['id'] ) &&  intval( $args['id'] ) ) {
            $tax = $tax->where( 'id', '=', $args['id'] );
        }

        if ( isset( $args['account'] ) &&  intval( $args['account'] ) ) {
            $tax = $tax->where( 'account', '=', $args['account'] );
        }

        if ( $args['number'] != -1 ) {
            $tax = $tax->skip( $args['offset'] )->take( $args['number'] );
        }

        $items = $tax->get()->toArray();

        if ( $args['output_by'] == 'object' ) {
            $items = erp_array_to_object( $items );
        }

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

function erp_ac_tax_component_fields() {

    erp_html_form_input( array(
        'name'        => 'items_id[]',
        'type'        => 'hidden',
    ) );

    erp_html_form_input( array(
        'name'        => 'component_name[]',
        'type'        => 'text',
        'placeholder' => __( 'Component name', 'erp' ),
        'required'    => true,
    ) );

    erp_html_form_input( array(
        'name'        => 'agency_name[]',
        'type'        => 'text',
        'placeholder' => __( 'Agency name', 'erp' ),
        'required'    => true
    ) );

    erp_html_form_input( array(
        'name'        => 'tax_rate[]',
        'type'        => 'text',
        'placeholder' => __( 'Rate(0.00%)', 'erp' ),
        'required'    => true
    ) );
}

function erp_ac_tax_component_field_with_value() {

    erp_html_form_input( array(
        'name'        => 'items_id[]',
        'type'        => 'hidden',
        'value'       => '{{items.id}}',
    ) );

    erp_html_form_input( array(
        'name'        => 'component_name[]',
        'type'        => 'text',
        'placeholder' => __( 'Component name', 'erp' ),
        'required'    => true,
        'value'       => '{{items.component_name}}'
    ) );

    erp_html_form_input( array(
        'name'        => 'agency_name[]',
        'type'        => 'text',
        'value'       => '{{items.agency_name}}',
        'placeholder' => __( 'Agency name', 'erp' ),
        'required'    => true
    ) );

    erp_html_form_input( array(
        'name'        => 'tax_rate[]',
        'type'        => 'text',
        'value'       => '{{items.tax_rate}}',
        'placeholder' => __( 'Rate(0.00%)', 'erp' ),
        'required'    => true
    ) );
}

function erp_ac_new_tax_account( $postdata, $tax_id ) {
    $is_update = isset( $postdata['id'] ) && $postdata['id'] ? true : false;

    if ( $is_update ) {
        return true;
    }

    $receitvable_code = erp_ac_accounting_code_generator();

    $receivable_account = array(
        'system'  => 1,
        'name'    => $postdata['tax_name'] . ' Receivable',
        'type_id' => 12,
        'tax'     => $tax_id,
        'code'    => $receitvable_code
    );

    erp_ac_insert_chart( $receivable_account );

    $payable_code = erp_ac_accounting_code_generator();

    $payable_account = array(
        'system'  => 1,
        'name'    => $postdata['tax_name'] . ' Payable',
        'type_id' => 7,
        'tax'     => $tax_id,
        'code'    => $payable_code
    );

    erp_ac_insert_chart( $payable_account );
}

function erp_ac_accounting_code_generator() {
    $ledger = WeDevs\ERP\Accounting\Model\Ledger::select( 'code' )->get()->toArray();
    $ledger = wp_list_pluck( $ledger, 'code' );
    $code   = random_int( 0, 999 );

    if ( in_array( $code, $ledger ) ) {
        erp_ac_accounting_code_generator();
    }

    return $code;
}

function erp_ac_get_trans_unit_tax_rate( $items ) {
    $itms_tax = [];
    $tax_info = erp_ac_get_tax_info();

    foreach ( $items as $item ) {

        if ( $item['tax'] === '0' ) {
            continue;
        }

        $rate_total = isset( $itms_tax[$item['tax']]['tax_total'] ) ? $itms_tax[$item['tax']]['tax_total'] : 0;
        $itms_tax[$item['tax']]['tax_rate'] = $item['tax_rate'];
        $itms_tax[$item['tax']]['tax_total']= ( ( $item['tax_rate'] * $item['line_total'] )/100 ) + $rate_total;
    }

    $print_tax = [];

    foreach ( $itms_tax as $tax_id => $itm_tax ) {
        $print_tax[$tax_id] = [
            'label' => sprintf( '%1$s %2$s (%3$s)', $tax_info[$tax_id]['name'], $itm_tax['tax_rate'] . '%', $tax_info[$tax_id]['number'] ),
            'total_amount' => $itm_tax['tax_total']
        ];
    }

    return $print_tax;
}

function erp_ac_get_tax_account_from_tax_id( $tax_id, $type ) {
    $cache_key = 'erp-ac-tax-' . $tax_id . $type . md5( serialize( get_current_user_id() ) );
    $account_id = wp_cache_get( $cache_key, 'erp' );

    if ( false === $account_id ) {
        if ( $type == 'sales' ) {
            $accounts = WeDevs\ERP\Accounting\Model\Ledger::where( 'tax', '=', $tax_id )->with(['charts' => function($q) {
                return $q->where( 'class_id', '=', 2 );
            }])->get()->toArray();
        } else {
            $accounts = WeDevs\ERP\Accounting\Model\Ledger::where( 'tax', '=', $tax_id )->with(['charts' => function($q) {
                return $q->where( 'class_id', '=', 3 );
            }])->get()->toArray();
        }

        foreach ( $accounts as $account ) {
            if ( $account['charts'] ) {
                $account_id = $account['id'];
            }
        }

        wp_cache_set( $cache_key, $account_id, 'erp' );
    }

    return $account_id;
}

function erp_ac_get_tax_receivable_ledger() {
    $all_tax_id = array_keys( erp_ac_get_tax_dropdown() );

    $receivables = WeDevs\ERP\Accounting\Model\Ledger::whereIn( 'tax', $all_tax_id )->with(['charts' => function( $q ) {
                return $q->where( 'class_id', '=', 3 );
            }])->get()->toArray();

    foreach ( $receivables as $key => $receivable ) {
        if ( ! count( $receivable['charts'] ) ) {
            unset( $receivables[$key] );
        }
    }

    return $receivables;
}

function erp_ac_get_tax_payable_ledger() {
    $all_tax_id = array_keys( erp_ac_get_tax_dropdown() );

    $payables = WeDevs\ERP\Accounting\Model\Ledger::whereIn( 'tax', $all_tax_id )->with(['charts' => function( $q ) {
                return $q->where( 'class_id', '=', 2 );
            }])->get()->toArray();

    foreach ( $payables as $key => $payable ) {
        if ( ! count( $payable['charts'] ) ) {
            unset( $payables[$key] );
        }
    }

    return $payables;
}






