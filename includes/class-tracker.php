<?php
namespace WeDevs\ERP;

/**
 * Tracker class
 */
class Tracker extends \WeDevs_Insights {

    public function __construct() {

        $notice = __( 'Want to help make <strong>WP ERP</strong> even more awesome? Allow weDevs to collect non-sensitive diagnostic data and usage information.', 'erp' );

        parent::__construct( 'erp', 'WP ERP', WPERP_FILE, $notice );
    }

    /**
     * Get the extra data
     *
     * @return array
     */
    protected function get_extra_data() {
        $data = array(
            'active_modules' => get_option( 'erp_modules', [] ),
            'contacts'       => $this->get_people_count( 'contact' ),
            'customer'       => $this->get_people_count( 'customer' ),
            'vendor'         => $this->get_people_count( 'vendor' ),
            'sales'          => $this->transaction_type_count( 'sales' ),
            'expense'        => $this->transaction_type_count( 'expense' ),
        );

        return $data;
    }

    /**
     * Get people type count
     *
     * @param  string  $type
     *
     * @return integer
     */
    private function get_people_count( $type ) {
        return \WeDevs\ERP\Framework\Models\People::type( $type )->count();
    }

    private function transaction_type_count( $type ) {
        if ( ! class_exists( '\WeDevs\ERP\Accounting\Model\Transaction' ) ) {
            require_once WPERP_MODULES . '/accounting/includes/models/transaction.php';
        }

        return \WeDevs\ERP\Accounting\Model\Transaction::type( $type )->count();
    }
}
