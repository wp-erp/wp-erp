<?php

namespace WeDevs\ERP\Accounting\Includes\Classes;

class Ledger_Map {
    private static $instance = null;

    public $ledgers;

    private function __construct() {
        global $wpdb;

        $this->ledgers = $wpdb->get_results( "SELECT slug, id, chart_id, category_id, name, code FROM {$wpdb->prefix}erp_acct_ledgers", OBJECT_K );
    }

    public static function get_instance() {
        if ( null === static::$instance ) {
            static::$instance = new Ledger_Map;
        }

        return static::$instance;
    }

    public function get_ledger_id_by_slug( $slug ) {
        if ( ! empty( $this->ledgers[ $slug ] ) ) {
            return $this->ledgers[ $slug ]->id;
        }
        return false;
    }

    public function get_ledger_details_by_slug( $slug ) {
        if ( ! empty( $this->ledgers[ $slug ] ) ) {
            return $this->ledgers[ $slug ];
        }
        return false;
    }
}
