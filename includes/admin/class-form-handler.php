<?php
namespace WeDevs\ERP\Admin;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Admin form handler
 *
 * Handles all the form submission
 */
class Form_Handler {

    use Hooker;

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->action( 'erp_action_create_new_company', 'create_new_company' );
    }

    /**
     * Create a new company
     *
     * @return void
     */
    public function create_new_company() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-new-company' ) ) {
            wp_die( __( 'Cheating?', 'wp-erp' ) );
        }

        $posted   = array_map( 'strip_tags', $_POST );
        $posted   = array_map( 'trim', $posted );

        $required = array(
            'company_name' => __( 'Company name', 'wp-erp' ),
            'country'      => __( 'Country', 'wp-erp' )
        );
        $errors   = array();

        foreach ($required as $key => $value) {
            if ( ! isset( $posted[$key]) || empty( $posted[$key] ) || $posted[$key] == '-1' ) {
                $errors[] = sprintf( __( '%s is required', 'wp-erp' ), $value );
            }
        }

        if ( $errors ) {
            var_dump( $errors );
            die();
        }

        $args = array(
            'title'     => $posted['company_name'],
            'id'        => intval( $posted['company_id'] ),
            'logo'      => isset( $posted['company_logo_id'] ) ? absint( $posted['company_logo_id'] ) : 0,
            'address_1' => $posted['address_1'],
            'address_2' => $posted['address_2'],
            'city'      => $posted['city'],
            'state'     => $posted['state'],
            'zip'       => $posted['zip'],
            'country'   => $posted['country'],
            'currency'  => $posted['currency'],
            'phone'     => $posted['phone'],
            'fax'       => $posted['fax'],
            'mobile'    => $posted['mobile'],
            'website'   => $posted['website'],
        );
        $company_id = erp_create_company( $args );

        if ( $company_id ) {

            // if it's an update
            if ( true === $company_id ) {
                $redirect_to = admin_url( 'admin.php?page=erp-company&action=edit&msg=updated&id=' . $posted['company_id'] );
            } else {
                $redirect_to = admin_url( 'admin.php?page=erp-company&action=edit&msg=created&id=' . $company_id );
            }

            wp_redirect( $redirect_to );
            exit;
        }
    }
}

new Form_Handler();