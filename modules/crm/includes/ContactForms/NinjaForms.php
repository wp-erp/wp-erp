<?php

namespace WeDevs\ERP\CRM\ContactForms;

use WeDevs\ERP\Framework\Traits\Hooker;

class NinjaForms {
    use Hooker;

    public function __construct() {
        $this->filter( 'erp_contact_forms_plugin_list', 'add_to_plugin_list' );
        $this->action( 'crm_get_ninja_forms_forms', 'get_forms' );
        $this->action( 'nf_save_sub', 'after_form_submit' );
    }

    /**
     * Add Ninja Forms to the integration plugin list
     *
     * @param array
     *
     * @return array
     */
    public function add_to_plugin_list( $plugins ) {
        $plugins['ninja_forms'] = [
            'title'     => 'Ninja Forms',
            'is_active' => class_exists( 'Ninja_Forms' ),
        ];

        return $plugins;
    }

    /**
     * Get all Ninja Forms forms and their fields
     *
     * @return array
     */
    public function get_forms() {
        $forms          = [];
        $saved_settings = get_option( 'wperp_crm_contact_forms', '' );

        $nf = Ninja_forms();

        if ( version_compare( get_option( 'ninja_forms_version', '0.0.0' ), '3', '<' ) ) {
            /* Support for version < 3.0 */
            $form_ids = $nf->forms()->get_all();

            if ( !empty( $form_ids ) ) {
                foreach ( $form_ids as $form_id ) {
                    $form = $nf->form( $form_id );

                    $forms[ $form_id ] = [
                        'name'         => $form_id,
                        'title'        => $form->settings['form_title'],
                        'fields'       => [],
                        'contactGroup' => '0',
                        'contactOwner' => '0',
                    ];

                    foreach ( $form->fields as $i => $field ) {
                        $forms[ $form_id ]['fields'][ $field['id'] ] = $field['data']['label'];

                        if ( !empty( $saved_settings['ninja_forms'][ $form_id ]['map'][ $field['id'] ] ) ) {
                            $crm_option = $saved_settings['ninja_forms'][ $form_id ]['map'][ $field['id'] ];
                        } else {
                            $crm_option = '';
                        }

                        $forms[ $form_id ]['map'][ $field['id'] ] = !empty( $crm_option ) ? $crm_option : '';
                    }

                    if ( !empty( $saved_settings['ninja_forms'][ $form_id ]['contact_group'] ) ) {
                        $forms[ $form_id ]['contactGroup'] = $saved_settings['ninja_forms'][ $form_id ]['contact_group'];
                    }

                    if ( !empty( $saved_settings['ninja_forms'][ $form_id ]['contact_owner'] ) ) {
                        $forms[ $form_id ]['contactOwner'] = $saved_settings['ninja_forms'][ $form_id ]['contact_owner'];
                    }
                }
            }
        } else {
            /* Support for version >= 3.0 */
            $nf_forms = $nf->form()->get_forms();

            foreach ( $nf_forms as $i => $nform ) {
                $form_id       = $nform->get_id();
                $form_settings = $nform->get_settings();
                $fields        = $nf->form( $form_id )->get_fields();

                $forms[ $form_id ] = [
                    'name'         => $form_id,
                    'title'        => $form_settings['title'],
                    'fields'       => [],
                    'contactGroup' => '0',
                    'contactOwner' => '0',
                ];

                foreach ( $fields as $i => $field ) {
                    $field_id       = $field->get_id();
                    $field_settings = $field->get_settings();

                    $forms[ $form_id ]['fields'][ $field_id ] = $field_settings['label'];

                    if ( !empty( $saved_settings['ninja_forms'][ $form_id ]['map'][ $field_id ] ) ) {
                        $crm_option = $saved_settings['ninja_forms'][ $form_id ]['map'][ $field_id ];
                    } else {
                        $crm_option = '';
                    }

                    $forms[ $form_id ]['map'][ $field_id ] = !empty( $crm_option ) ? $crm_option : '';
                }

                if ( !empty( $saved_settings['ninja_forms'][ $form_id ]['contact_group'] ) ) {
                    $forms[ $form_id ]['contactGroup'] = $saved_settings['ninja_forms'][ $form_id ]['contact_group'];
                }

                if ( !empty( $saved_settings['ninja_forms'][ $form_id ]['contact_owner'] ) ) {
                    $forms[ $form_id ]['contactOwner'] = $saved_settings['ninja_forms'][ $form_id ]['contact_owner'];
                }
            }
        }

        return $forms;
    }

    /**
     * After Ninja Forms submission hook
     *
     * @since 1.2.7 stripping slashes from formData
     *
     * @return void
     */
    public function after_form_submit( $sub_id ) {
        $nf      = Ninja_forms();
        $form_id = 0;
        $data    = [];

        if ( version_compare( get_option( 'ninja_forms_version', '0.0.0' ), '3', '<' ) ) {
            /* Support for version < 3.0 */
            $sub     = $nf->sub( $sub_id );
            $form_id = $sub->form_id;
            $data    = $sub->field;
        } else {
            /* Support for version >= 3.0 */
            $sub = $nf->form()->get_sub( $sub_id );

            $formData = isset( $_POST['formData'] ) ? sanitize_text_field( wp_unslash( $_POST['formData'] ) ) : '';
            $formData = json_decode( $formData, true );

            $form_id = $formData['id'];

            foreach ( $formData['fields'] as $i => $field ) {
                $data[ $field['id'] ] = $field['value'];
            }
        }

        // first check if submitted form has settings or not
        $cfi_settings = get_option( 'wperp_crm_contact_forms', '' );

        // if we don't have any setting, then do not proceed
        if ( empty( $cfi_settings['ninja_forms'] ) ) {
            return;
        }

        $nf_settings = $cfi_settings['ninja_forms'];

        if ( in_array( $form_id, array_keys( $nf_settings ) ) ) {
            do_action( 'wperp_integration_ninja_forms_form_submit', $data, 'ninja_forms', $form_id );
        }
    }
}
