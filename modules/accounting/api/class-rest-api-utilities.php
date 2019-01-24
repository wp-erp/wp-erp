<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Utilities_Controller extends \WeDevs\ERP\API\REST_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'accounting/v1/utilities';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/people-address' . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_people_address' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/payment-methods', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_payment_methods' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

    }

    /**
     * Return formatted address of a people
     *
     * @param $id
     * @return string
     */
    public function get_people_address( $id ) {
        global $wpdb;

        $sql = "SELECT
            street_1, street_2, city, state, postal_code, country
            FROM {$wpdb->prefix}erp_peoples";

        $row = $wpdb->get_row($sql, ARRAY_A);

        return erp_acct_format_people_address($row);
    }

    /**
     * Return available payment methods
     *
     * @return array
     */
    public function get_payment_methods() {
        global $wpdb;

        $sql = "SELECT id, name
            FROM {$wpdb->prefix}erp_acct_payment_methods";

        $row = $wpdb->get_results($sql, ARRAY_A);

        return $row;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object|array $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {

        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

}
