<?php
namespace WeDevs\ERP\Accounting\Includes;

class Ledger_Map {
    private static $instance = null;

    public $ledgers;

    private function __construct() {
        global $wpdb;

        $sql = "SELECT id, chart_id, category_id, name, slug, code 
            FROM {$wpdb->prefix}erp_acct_ledgers WHERE system = 1";

        $this->ledgers = $wpdb->get_results($sql);
    }

    public static function getInstance() {
        if (static::$instance == null) {
            static::$instance = new Ledger_Map;
        }

        return static::$instance;
    }

    public function get_ledger_id_by_slug( $slug ) {
        foreach ( $this->ledgers as $key => $value ) {
            if ( $value->slug === $slug ) {
                return (int) $value->id;
            }
        }

        return false;
    }
}
