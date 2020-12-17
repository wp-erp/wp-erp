<?php

namespace WeDevs\ERP\Framework\Traits;

/**
 * Class Hooker
 */
trait Api {

    /**
     * Format item's collection for response
     *
     * @param object $response
     * @param object $request
     * @param int    $total_items
     *
     * @return object
     */
    public function format_collection_response( $response, $request, $total_items ) {
        if ( $total_items === 0 ) {
            return $response;
        }

        // Store pagation values for headers then unset for count query.
        $per_page = (int) ( ! empty( $request['per_page'] ) ? $request['per_page'] : 20 );
        $page     = (int) ( ! empty( $request['page'] ) ? $request['page'] : 1 );

        $response->header( 'X-WP-Total', (int) $total_items );

        $max_pages = ceil( $total_items / $per_page );

        $response->header( 'X-WP-TotalPages', (int) $max_pages );
        $base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

        if ( $page > 1 ) {
            $prev_page = $page - 1;

            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }

        if ( $max_pages > $page ) {
            $next_page = $page + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    }

    /**
     * Adds multiple links to the response.
     *
     * @param object $response
     * @param object $item
     * @param array  $additional_fields
     *
     * @return object
     */
    public function add_links( $response, $item, $additional_fields = [] ) {
        $response->data['_links'] = $this->prepare_links( $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare links for the request.
     *
     * @param object $item
     * @param array $additional_fields
     *
     * @return array links for the given user
     */
    public function prepare_links( $item, $additional_fields = [] ) {
        if ( empty( $additional_fields ) ) {
            $links = [
                'self' => [
                    'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $item->id ) ),
                ],
                'collection' => [
                    'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
                ],
            ];

            return $links;
        }

        $item = (array) $item;

        $namespace = $additional_fields['namespace'];
        $rest_base = $additional_fields['rest_base'];

        if ( empty( $item['id'] ) && isset( $additional_fields['id'] ) ) {
            $item['id'] = $additional_fields['id'];
        }

        if ( empty( $item['id'] ) && empty( $additional_fields['id'] ) ) {
            $item['id'] = '';
        }

        $links = [
            'self' => [
                'href' => rest_url( sprintf( '%s/%s/%d', $namespace, $rest_base, $item['id'] ) ),
            ],
            'collection' => [
                'href' => rest_url( sprintf( '%s/%s', $namespace, $rest_base ) ),
            ],
        ];

        return $links;
    }
}
