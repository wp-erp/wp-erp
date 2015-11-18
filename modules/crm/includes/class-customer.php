<?php
namespace WeDevs\ERP\CRM;

/**
* Customer Class
*
* @since 1.0
*
* @package WP-ERP|CRM
*/
class Customer extends \WeDevs\ERP\People {

    /**
     * Load parent constructor
     *
     * @since 1.0
     *
     * @param int|object $customer
     */
    public function __construct( $customer = null ) {
        parent::__construct( $customer );
    }

    /**
     * Get the user info as an array
     *
     * @return array
     */
    public function to_array() {
        $fields = array(
            'id'         => 0,
            'user_id'    => '',
            'first_name' => '',
            'last_name'  => '',
            'company'    => '',
            'avatar'     => array(
                'id'  => 0,
                'url' => ''
            ),
            'email'       => '',
            'phone'       => '',
            'mobile'      => '',
            'website'     => '',
            'fax'         => '',
            'street_1'    => '',
            'street_2'    => '',
            'city'        => '',
            'country'     => '',
            'state'       => '',
            'postal_code' => '',
            'notes'       => '',
            'other'       => '',
        );

        if ( $this->id ) {
            foreach ( $this->data as $key => $value ) {
                $fields[$key] = $value;
            }
        }

        return apply_filters( 'erp_crm_get_customer_fields', $fields, $this->data, $this->id );
    }

}

