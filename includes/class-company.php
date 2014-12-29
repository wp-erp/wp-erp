<?php
namespace WeDevs\ERP;

/**
 * Company class
 */
class Company {

    /**
     * Initialize a company
     *
     * @param int|object  the company numeric id or a wpdb row
     */
    public function __construct( $company ) {
        if ( is_object( $company ) ) {

            $this->populate( $company );

        } elseif ( is_int( $company ) ) {

            $fetched = $this->get_by_id( $company );
            $this->populate( $fetched );

        }
    }

    /**
     * Magic method to get company data values
     *
     * @param  string
     *
     * @return string
     */
    public function __get( $key ) {
        if ( isset( $this->data->$key ) ) {
            return $this->data->$key;
        }
    }

    /**
     * [populate description]
     *
     * @param  object  the company wpdb object
     *
     * @return void
     */
    private function populate( $company ) {
        $this->id   = $company->id;
        $this->name = $company->name;
        $this->data = $company;
    }

    /**
     * Get a company by ID
     *
     * @param  int  company id
     *
     * @return object  wpdb object
     */
    private function get_by_id( $company_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_companies WHERE id = %d", $company_id ) );
    }

    /**
     * Get the company logo
     *
     * @return string the HTML image attribute
     */
    public function get_logo() {
        $logo_id = (int) $this->logo;

        if ( ! $logo_id ) {
            $url = $this->placeholder_logo();
        } else {
            $url = wp_get_attachment_image_src( $logo_id, 'medium' )[0];
        }

        $image = sprintf( '<img src="%s" alt="%s" />', esc_url( $url ), esc_attr( $this->name ) );

        return $image;
    }

    /**
     * [placeholder_logo description]
     *
     * @return string placeholder image url
     */
    public function placeholder_logo() {
        $url = WPERP_ASSETS . '/images/placeholder.png';

        return apply_filters( 'erp_placeholder_image', $url );
    }

    /**
     * Get formatted address of the company
     *
     * @return string address
     */
    public function get_formatted_address() {
        $country = \WeDevs\ERP\Countries::instance();

        return $country->get_formatted_address( array(
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'city'      => $this->city,
            'state'     => $this->state,
            'postcode'  => $this->zip,
            'country'   => $this->country
        ) );
    }

    /**
     * [get_edit_url description]
     *
     * @return string the url
     */
    public function get_edit_url() {
        $url = add_query_arg(
            array( 'action' => 'view', 'id' => $this->id ),
            admin_url( 'admin.php?page=erp-company' )
        );

        return $url;
    }
}