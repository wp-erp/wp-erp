<?php

namespace WeDevs\ERP\CRM\ContactForms;

use WeDevs\ERP\Framework\Traits\Hooker;
use WP_Query;
use WPCF7_ContactForm;

class CF7 {
    use Hooker;

    public function __construct() {
        $this->filter( 'erp_contact_forms_plugin_list', 'add_to_plugin_list' );
        $this->action( 'crm_get_contact_form_7_forms', 'get_forms' );
        $this->action( 'wpcf7_submit', 'after_form_submit' );
    }

    /**
     * Add Contact Form 7 to the integration plugin list
     *
     * @param array
     *
     * @return array
     */
    public function add_to_plugin_list( $plugins ) {
        $plugins['contact_form_7'] = [
            'title'     => __( 'Contact Form 7', 'erp' ),
            'is_active' => class_exists( 'WPCF7_ContactForm' ),
        ];

        return $plugins;
    }

    /**
     * Get all Contact Form 7 forms and their fields
     *
     * @return array
     */
    public function get_forms() {
        $forms = [];

        $args = [
            'post_type'      => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ];

        $cf7_query = new WP_Query( $args );

        if ( !$cf7_query->have_posts() ) {
            return $forms;
        } else {
            while ( $cf7_query->have_posts() ) {
                $cf7_query->the_post();
                global $post;

                $cf7 = WPCF7_ContactForm::get_instance( $post->ID );

                $saved_settings = get_option( 'wperp_crm_contact_forms', '' );

                $forms[ $post->ID ] = [
                    'name'         => $post->post_name,
                    'title'        => $post->post_title,
                    'fields'       => [],
                    'contactGroup' => '0',
                    'contactOwner' => '0',
                ];

                foreach ( $cf7->collect_mail_tags() as $tag ) {
                    $forms[ $post->ID ]['fields'][ $tag ] = '[' . $tag . ']';

                    if ( !empty( $saved_settings['contact_form_7'][ $post->ID ]['map'][ $tag ] ) ) {
                        $crm_option = $saved_settings['contact_form_7'][ $post->ID ]['map'][ $tag ];
                    } else {
                        $crm_option = '';
                    }

                    $forms[ $post->ID ]['map'][ $tag ] = !empty( $crm_option ) ? $crm_option : '';
                }

                if ( !empty( $saved_settings['contact_form_7'][ $post->ID ]['contact_group'] ) ) {
                    $forms[ $post->ID ]['contactGroup'] = $saved_settings['contact_form_7'][ $post->ID ]['contact_group'];
                }

                if ( !empty( $saved_settings['contact_form_7'][ $post->ID ]['contact_owner'] ) ) {
                    $forms[ $post->ID ]['contactOwner'] = $saved_settings['contact_form_7'][ $post->ID ]['contact_owner'];
                }
            }
        }

        return $forms;
    }

    /**
     * After Contact Form 7 submission hook
     *
     * @return void
     */
    public function after_form_submit() {
        if ( ! isset( $_POST['_wpcf7'] ) ) {
            return;
        }

        // first check if submitted form has settings or not
        $cfi_settings = get_option( 'wperp_crm_contact_forms', '' );

        // if we don't have any setting, then do not proceed
        if ( empty( $cfi_settings['contact_form_7'] ) ) {
            return;
        }

        $cf7_settings = $cfi_settings['contact_form_7'];

        if ( in_array( sanitize_text_field( wp_unslash( $_POST['_wpcf7'] ) ), array_keys( $cf7_settings ) ) ) {
            do_action( 'wperp_integration_contact_form_7_form_submit', map_deep( wp_unslash( $_POST ), 'sanitize_text_field' ), 'contact_form_7', sanitize_text_field( wp_unslash( $_POST['_wpcf7'] ) ) );
        }
    }
}
