<?php

namespace WeDevs\ERP;

/**
 * Tracker class
 */
class Tracker {
    private static $instance = null;

    private $insights;

    private $client;

    /**
     * Singleton instance
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->client = new \Appsero\Client( 'd8539710-7e93-422a-a293-11739b118d6a', 'WP ERP', WPERP_FILE );

        $this->insights = $this->client->insights()
            ->add_extra( [ $this, 'get_extra_data' ] );
    }

    /**
     * Get the extra data
     *
     * @return array
     */
    public function get_extra_data() {
        $data = [
            'active_modules' => get_option( 'erp_modules', [] ),
            'contacts'       => $this->get_people_count( 'contact' ),
            'customer'       => $this->get_people_count( 'customer' ),
            'employees'       => $this->get_people_count( 'employee' ),
            'vendor'         => $this->get_people_count( 'vendor' ),
            // 'sales'          => $this->transaction_type_count( 'sales' ),
            // 'expense'        => $this->transaction_type_count( 'expense' ),
        ];

        return $data;
    }

    /**
     * Get people type count
     *
     * @param string $type
     *
     * @return int
     */
    private function get_people_count( $type ) {
        return \WeDevs\ERP\Framework\Models\People::type( $type )->count();
    }

    /**
     * Initialize appsero insights
     */
    public function init() {
        $this->insights->init();
    }

    /**
     * Forcefully optin
     */
    public function optin() {
        $this->insights->optin();
    }

    /**
     * Forcefully optout
     */
    public function optout() {
        $this->insights->optout();
    }

    /**
     * Tracking not allowed
     */
    public function not_allowed() {
        return ! $this->insights->tracking_allowed();
    }
}
