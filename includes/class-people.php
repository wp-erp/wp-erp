<?php
namespace WeDevs\ERP;

/**
 * People Class
 */
class People extends Item {

    /**
     * Generate people data
     *
     * @param  object  the item wpdb object
     *
     * @return void
     */
    protected function populate( $item ) {
        $this->id   = (int) $item->id;
        $this->data = $item;
    }

    /**
     * Fetch a single people
    *
     * @param int $people_id
     *
     * @return object
     */
    protected function get_by_id( $people_id ) {
        return erp_get_people( $people_id );
    }

    /**
     * Check if this people is a WP_User type
     *
     * @return boolean
     */
    function is_wp_user() {
        return intval( $this->user_id ) !== 0;
    }

    /**
     * Get full name
     *
     * @return string
     */
    function get_full_name() {
        if ( in_array( 'company', $this->types ) ) {
            return $this->company;

        } elseif ( $this->is_wp_user() ) {
            $user = \get_user_by( 'id', $this->user_id );

            if ( ! empty( $user->first_name ) ) {
                return $user->first_name . ' ' . $user->last_name;
            }

            return $user->display_name;
        } else {
            return $this->first_name . ' ' . $this->last_name;
        }
    }

    /**
     * Get email address of a user
     *
     * @return string
     */
    function get_email() {
        if ( $this->is_wp_user() ) {
            return \get_user_by( 'id', $this->user_id )->user_email;
        } else {
            return $this->email;
        }
    }

    /**
     * Get website address of a user
     *
     * @since 1.0
     *
     * @return string
     */
    function get_website() {
        if ( $this->is_wp_user() ) {
            $user = \get_user_by( 'id', $this->user_id );
            return ( $user->user_url ) ? erp_get_clickable( 'url', $user->user_url ) : '—';
        } else {
            return ( $this->website ) ? erp_get_clickable( 'url', $this->website ) : '—';
        }
    }

    /**
     * Get meta data of a user
     *
     * @param string $meta_key
     * @param string $meta_value
     */
    function get_meta( $meta_key, $single = true ) {
        if ( $this->is_wp_user() ) {
            return \get_user_meta( $this->user_id, $meta_key, $single );
        } else {
            return \erp_people_get_meta( $this->id, $meta_key, $single );
        }
    }

    /**
     * Add meta data to a user
     *
     * @param string $meta_key
     * @param string $meta_value
     */
    function add_meta( $meta_key, $meta_value ) {
        if ( $this->is_wp_user() ) {
            \add_user_meta( $this->user_id, $meta_key, $meta_value );
        } else {
            \erp_people_add_meta( $this->id, $meta_key, $meta_value );
        }
    }

    /**
     * Update meta data to a user
     *
     * @param string $meta_key
     * @param string $meta_value
     */
    function update_meta( $meta_key, $meta_value ) {
        if ( $this->is_wp_user() ) {
            \update_user_meta( $this->user_id, $meta_key, $meta_value );
        } else {
            \erp_people_update_meta( $this->id, $meta_key, $meta_value );
        }
    }

    /**
     * Delete meta data to a user
     *
     * @param string $meta_key
     * @param string $meta_value
     */
    function delete_meta( $meta_key ) {
        if ( $this->is_wp_user() ) {
            \delete_user_meta( $this->user_id, $meta_key );
        } else {
            \erp_people_delete_meta( $this->id, $meta_key );
        }
    }

}
