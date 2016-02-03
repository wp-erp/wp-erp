<?php
namespace WeDevs\ERP\CRM;

/**
* Customer Class
*
* @since 1.0
*
* @package WP-ERP|CRM
*/
class Contact extends \WeDevs\ERP\People {

    protected $contact_type;
    /**
     * Load parent constructor
     *
     * @since 1.0
     *
     * @param int|object $contact
     */
    public function __construct( $contact = null, $type = null ) {
        parent::__construct( $contact );
        $this->contact_type = $type;
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
            'life_stage'  => '',
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
            'type'        => '',
            'notes'       => '',
            'other'       => '',
            'social'      => []
        );

        $social_field = erp_crm_get_social_field();

        foreach ( $social_field as $social_key => $social_value ) {
            $fields['social'][$social_key] = '';
        }

        if ( $this->id ) {
            foreach ( $this->data as $key => $value ) {
                $fields[$key] = $value;
            }

            $avatar_id              = (int) $this->get_meta( 'photo_id', true );
            $fields['avatar']['id'] = $avatar_id;

            if ( $avatar_id ) {
                $fields['avatar']['url'] = wp_get_attachment_url( $avatar_id );
            }

            foreach ( $fields['social'] as $key => $value ) {
                $fields['social'][$key] = $this->get_meta( $key, true );
            }

            $fields['life_stage'] = $this->get_meta( 'life_stage', true );
        }

        return apply_filters( 'erp_crm_get_ '. $this->contact_type . '_fields', $fields, $this->data, $this->id );
    }

    /**
     * Get single customer page view url
     *
     * @return string the url
     */
    public function get_details_url() {
        if ( $this->id ) {

            if ( $this->contact_type == 'contact' ) {
                return admin_url( 'admin.php?page=erp-sales-customers&action=view&id=' . $this->id );
            }

            if ( $this->contact_type == 'company' ) {
                return admin_url( 'admin.php?page=erp-sales-companies&action=view&id=' . $this->id );
            }
        }
    }

    /**
     * Get an customer avatar
     *
     * @param  integer  avatar size in pixels
     *
     * @return string  image with HTML tag
     */
    public function get_avatar( $size = 32 ) {

        if ( $this->id ) {

            $user_photo_id = $this->get_meta( 'photo_id', true );

            if ( ! empty( $user_photo_id ) ) {
                $image = wp_get_attachment_thumb_url( $user_photo_id );
                return sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
            }
        }

        return get_avatar( $this->email, $size );
    }

}

