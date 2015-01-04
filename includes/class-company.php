<?php
namespace WeDevs\ERP;

/**
 * Company class
 */
class Company extends Item {

    /**
     * Get a company by ID
     *
     * @param  int  company id
     *
     * @return object  wpdb object
     */
    protected function get_by_id( $company_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_companies WHERE id = %d", $company_id ) );
    }

    /**
     * Check if a company has logo
     *
     * @return boolean
     */
    public function has_logo() {
        return (int) $this->logo;
    }

    /**
     * Get the company logo
     *
     * @return string the HTML image attribute
     */
    public function get_logo() {
        $logo_id = (int) $this->logo;

        if ( ! $logo_id ) {
            $url   = $this->placeholder_logo();
        } else {
            $image = wp_get_attachment_image_src( $logo_id, 'medium' );
            $url   = $image[0];
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
            array( 'action' => 'edit', 'id' => $this->id ),
            admin_url( 'admin.php?page=erp-company' )
        );

        return $url;
    }

    /**
     * Check if the employee belongs to the company
     *
     * @param  int   employee id
     *
     * @return boolean
     */
    public function has_employee( $employee_id ) {
        global $wpdb;

        $sql = "SELECT id FROM {$wpdb->prefix}erp_hr_employees WHERE employee_id = %d AND company_id = %d";
        $row = $wpdb->get_row( $wpdb->prepare( $sql, $employee_id, $this->id ) );

        if ( $row ) {
            return true;
        }

        return false;
    }
}