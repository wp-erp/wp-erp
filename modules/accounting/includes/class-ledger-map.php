<?php

namespace WeDevs\ERP\Accounting\Includes;

class Ledger_Map {
    private static $instance = null;

    public $ledgers;

    private function __construct() {
        global $wpdb;

        $sql = "SELECT slug, id, chart_id, category_id, name, code 
            FROM {$wpdb->prefix}erp_acct_ledgers WHERE system = 1";

        $this->ledgers = $wpdb->get_results( $sql, OBJECT_K );
    }

    public static function getInstance() {
        if ( static::$instance == null ) {
            static::$instance = new Ledger_Map;
        }

        return static::$instance;
    }

    public function get_ledger_id_by_slug( $slug ) {
        if ( $this->ledgers[$slug] ) {
            return $this->ledgers[$slug]->id;
        }
        return false;
    }

    public function get_ledger_details_by_slug( $slug ) {
        if ( $this->ledgers[$slug] ) {
            return $this->ledgers[$slug];
        }
        return false;
    }
}
