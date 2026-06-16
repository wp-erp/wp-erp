<?php

namespace WeDevs\ERP\CRM\ContactForms;

/**
 * ERP Settings Contact Form class
 */
class ERPSettingsContactForms {
    use ContactForms;

    protected $crm_options        = [];
    protected $active_plugin_list = [];
    protected $forms              = [];
    public $sub_sections          = [];

    /**
     * Class constructor
     */
    public function __construct() {
        $this->crm_options        = $this->get_crm_contact_options();
        $this->active_plugin_list = $this->get_active_plugin_list();
        $this->sub_sections       = $this->get_subsections();

        $this->filter( 'erp_settings_crm_section_fields', 'crm_contact_forms_section_fields', 10, 2 );

        foreach ( $this->active_plugin_list as $slug => $plugin ) {
            $this->forms[ $slug ] = apply_filters( "crm_get_{$slug}_forms", [] );
        }
    }

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Get contact form settings
     *
     * @since 1.9.0
     *
     * @return array $crm_contact_forms_settings
     */
    public function get_scripts_data() {
        $crm_contact_forms_settings = [
            'nonce'             => wp_create_nonce( 'erp_settings_contact_forms' ),
            'plugins'           => array_keys( $this->active_plugin_list ),
            'forms'             => $this->forms,
            'mappedData'        => get_option( 'wperp_crm_contact_forms', '' ),
            'crmOptions'        => $this->crm_options,
            'scriptDebug'       => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
            'contactGroups'     => erp_crm_get_contact_groups_list(),
            'contactOwners'     => erp_crm_get_crm_user_dropdown(),
            'i18n'              => [
                'notMapped'             => __( 'Not Set', 'erp' ),
                'labelOK'               => __( 'OK', 'erp' ),
                'labelContactGroups'    => __( 'Contact Group', 'erp' ),
                'labelSelectGroup'      => __( 'Select Contact Group', 'erp' ),
                'labelContactOwner'     => __( 'Contact Owner', 'erp' ),
                'labelSelectOwner'      => __( 'Select Owner', 'erp' ),
            ],
        ];

        return $crm_contact_forms_settings;
    }

    /**
     * Settings fields for contact forms
     *
     * @param array $fields
     * @param array $sections
     *
     * @return array
     */
    public function crm_contact_forms_section_fields( $fields, $sections ) {
        $plugins = $this->active_plugin_list;

        if ( empty( $plugins ) ) {
            $fields['contact_forms'] = [
                [
                    'title' => __( 'Contact Forms Integration', 'erp' ),
                    'type'  => 'title',
                    'desc'  => sprintf(
                                '%s' . __( 'No supported contact form plugin is currently active. WP ERP has built-in support for <strong>Contact Form 7</strong> and <strong>Ninja Forms</strong>.', 'erp' ) . '%s',
                                '<section class="notice notice-warning cfi-hide-submit mt-20"><p>',
                                '</p></section>'
                            ),
                    'id' => 'contact_form_options',
                ],
            ];

            return $fields;
        }

        $keys        = array_keys( $plugins );
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : $keys[0];
        $forms       = $this->forms[ $sub_section ];

        if ( empty( $forms ) ) {
            /* If no form created with respective plugin this notice will show.
                Also if there is no function hook to the "crm_get_{$slug}_forms",
                filter we'll see this notice */
            $fields['contact_forms'] = [
                [
                    'title' => $plugins[ $sub_section ]['title'],
                    'type'  => 'title',
                    'desc'  => sprintf(
                                '%s' . __( "You don't have any form created with %s!", 'erp' ) . '%s',
                                '<section class="notice notice-warning cfi-hide-submit"><p>',
                                $plugins[ $sub_section ]['title'],
                                '</p></section>'
                            ),
                    'id' => 'section_' . $sub_section,
                ],
            ];
        } else {
            $fields['contact_forms']['sub_sections']   = $this->sub_sections;
            $fields['contact_forms']['localized_data'] = $this->get_scripts_data();

            foreach ( $forms as $form_id => $form ) {
                $fields['contact_forms'][] = [
                    'title' => $form['title'],
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'section_' . $form['name'],
                ];

                $fields['contact_forms'][] = [
                    'plugin'        => $sub_section,
                    'form_id'       => $form_id,
                    'type'          => 'contact_form_options',
                ];

                $fields['contact_forms'][] = [ 'type' => 'sectionend', 'id' => 'section_' . $form['name'] ];
            }
        }

        return $fields;
    }

    /**
     * Get sub sections based on active plugin lists
     *
     * @since 1.9.0
     *
     * @return array $sub_sections
     */
    private function get_subsections() {
        $sub_sections = [];

        foreach ( $this->active_plugin_list as $key => $active_plugin ) {
            if ( $active_plugin['is_active'] ) {
                $sub_sections[ $key ] = $active_plugin['title'];
            }
        }

        return $sub_sections;
    }

    /**
     * Ajax hook function to save the ERP Settings
     *
     * @return void prints json object
     */
    public function save_erp_settings() {
        $response = [
            'success' => false,
            'msg'     => null,
        ];

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp_settings_contact_forms' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if ( ! erp_crm_is_current_user_manager() ) {
            $response['msg'] = __( 'Unauthorized operation', 'erp' );
        }

        if ( ! empty( $_POST['plugin'] ) && ! empty( $_POST['formId'] ) && ! empty( $_POST['map'] ) ) {
            $required_options = $this->get_required_crm_contact_options();
            $map_data         = array_map( 'sanitize_text_field', wp_unslash( $_POST['map'] ) );

            // if map contains full_name, then remove first and last names from required options
            if ( in_array( 'full_name', $map_data, true ) ) {
                $index = array_search( 'first_name', $required_options );
                unset( $required_options[ $index ] );

                $index = array_search( 'last_name', $required_options, true );
                unset( $required_options[ $index ] );

                array_unshift( $required_options, 'full_name' );
            }

            $diff = array_diff( $required_options, $map_data );

            if ( ! empty( $diff ) ) {
                $required_options = array_map( function ( $option ) {
                    return ucwords( str_replace( '_', ' ', $option ) );
                }, $required_options );

                $response['msg'] = sprintf(
                    __( '%s fields are required', 'erp' ),
                    implode( ', ', $required_options )
                );
            } elseif ( empty( $_POST['contactOwner'] ) ) {
                $response['msg'] = __( 'Please set a contact owner.', 'erp' );
            } else {
                $settings = get_option( 'wperp_crm_contact_forms' );

                $settings[ sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) ][ sanitize_text_field( wp_unslash( $_POST['formId'] ) ) ] = [
                    'map'           => $map_data,
                    'contact_group' => isset( $_POST['contactGroup'] ) ? sanitize_text_field( wp_unslash( $_POST['contactGroup'] ) ) : '',
                    'contact_owner' => isset( $_POST['contactOwner'] ) ? sanitize_text_field( wp_unslash( $_POST['contactOwner'] ) ) : '',
                ];

                update_option( 'wperp_crm_contact_forms', $settings );

                $response = [
                    'success' => true,
                    'msg'     => __( 'Settings saved successfully', 'erp' ),
                ];
            }
        } elseif ( empty( $_POST['forms'] ) ) {
            $response['msg'] = __( 'No settings data found', 'erp' );
        }

        wp_send_json( $response );
    }

    /**
     * Ajax hook function to reset ERP Settings for a form
     *
     * @return void prints json object
     */
    public function reset_erp_settings() {
        $response = [
            'success' => false,
            'msg'     => null,
        ];

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp_settings_contact_forms' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if ( ! erp_crm_is_current_user_manager() ) {
            $response['msg'] = __( 'Unauthorized operation', 'erp' );
        } elseif ( ! empty( $_POST['plugin'] ) && ! empty( $_POST['formId'] ) ) {
            $settings = get_option( 'wperp_crm_contact_forms' );
            $plugin   = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
            $form_id  = sanitize_text_field( wp_unslash( $_POST['formId'] ) );

            if ( ! empty( $settings[ $plugin ][ $form_id ] ) ) {
                $map = $settings[ $plugin ][ $form_id ]['map'];

                unset( $settings[ $plugin ][ $form_id ] );

                update_option( 'wperp_crm_contact_forms', $settings );

                // map the $map array to null values
                $map = array_map( function () {
                    return null;
                }, $map );

                $response = [
                    'success'      => true,
                    'msg'          => __( 'Settings reset successfully', 'erp' ),
                    'map'          => $map,
                    'contactGroup' => 0,
                    'contactOwner' => 0,
                ];
            } else {
                $response['msg'] = __( 'Nothing to reset', 'erp' );
            }
        }

        wp_send_json( $response );
    }
}
