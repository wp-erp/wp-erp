<?php
use \WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * ERP Settings Contact Form class
 */
class ERP_Settings_Contact_Forms extends ERP_Settings_Page {

    use WeDevs\ERP\Framework\Traits\Ajax;

    public $id = '';
    public $label = '';
    public $sections = array();

    protected $contact_form_integration = null;
    protected $crm_options = array();
    protected $active_plugin_list = array();
    protected $forms = array();

    function __construct() {
        $this->contact_form_integration = \WeDevs\ERP\CRM\Contact_Forms_Integration::init();
        $this->crm_options = $this->contact_form_integration->get_crm_contact_options();
        $this->active_plugin_list = $this->contact_form_integration->get_active_plugin_list();

        $this->hook_functions();

        wp_enqueue_style( 'erp-sweetalert' );
        wp_enqueue_script( 'erp-sweetalert' );
        wp_enqueue_script( 'erp-vuejs' );
        wp_enqueue_script( 'erp-settings-contact-forms', WPERP_CRM_ASSETS . '/js/erp-settings-contact-forms.js', array( 'erp-vuejs', 'jquery', 'erp-sweetalert' ), WPERP_VERSION, true );
        wp_localize_script( 'erp-settings-contact-forms', 'crmContactFormsSettings', $this->set_localized_object() );

        $this->id = 'contact_forms';
        $this->label = __( 'Contact Forms', 'wp-erp' );
        $this->sections = $this->get_sections();
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
     * The localized JS object
     *
     * @return array
     */
    public function set_localized_object() {
        foreach ( $this->active_plugin_list as $slug => $plugin ) {
            $this->forms[ $slug ] = apply_filters( "crm_get_{$slug}_forms", array() );
        }

        return array(
            'nonce'         => wp_create_nonce( 'erp_settings_contact_forms' ),
            'plugins'       => array_keys( $this->active_plugin_list ),
            'forms'         => $this->forms,
            'mappedData'    => get_option( 'wperp_crm_contact_forms', '' ),
            'crmOptions'    => $this->crm_options,
            'notMapped'     => __( 'Not Set', 'wp-erp' ),
            'scriptDebug'   => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
            'labelOK'       => __( 'OK', 'wp-erp' ),
        );
    }

    /**
     * Hook various functions
     *
     * @return void
     */
    public function hook_functions() {
        // form functions
        add_filter( 'crm_get_contact_form_7_forms', array( $this, 'crm_get_contact_form_7_forms' ) );
        add_filter( 'crm_get_ninja_forms_forms', array( $this, 'crm_get_ninja_forms_forms' ) );

        // option field type
        add_action( 'erp_admin_field_match_form_fields', array( $this, 'output_match_form_fields' ) );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array(
                'title' => __( 'Contact Forms Integration', 'wp-erp' ),
                'type' => 'title',
                'desc' => '',
                'id' => 'contact_form_options'
            ),

            array( 'type' => 'sectionend', 'id' => 'contact_form_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_example', $fields );
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array();

        if ( !empty( $this->active_plugin_list ) ) {
            foreach ( $this->active_plugin_list as $slug => $plugin ) {
                $sections[ $slug ] = $plugin['title'];
            }
        }

        return $sections;
    }

    /**
     * Get sections fields
     *
     * @param string $section current settings tab section
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        $fields = array();

        foreach ( $this->active_plugin_list as $slug => $plugin ) {
            $forms = $this->forms[ $slug ];

            if ( empty( $forms ) ) {
                /* If no form created with respective plugin this notice will show.
                   Also if there is no function hook to the "crm_get_{$slug}_forms",
                   filter we'll see this notice */
                $fields[ $slug ] = array(
                    array(
                        'title' => $plugin['title'],
                        'type' => 'title',
                        'desc' => sprintf(
                                    __( '%sYou don\'t have any form created with %s!%s', 'wp-erp' ),
                                    '<section class="notice notice-warning"><p>',
                                    $plugin['title'],
                                    '</p></section>'
                                ),
                        'id' => 'section_' . $slug
                    ),

                    array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),
                );
            } else {
                foreach ( $forms as $form_id => $form ) {
                    $fields[ $slug ][] = array(
                        'title' => $form['title'],
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'section_' . $form['name']
                    );

                    $fields[ $slug ][] = array(
                        'plugin'        => $slug,
                        'form_id'       => $form_id,
                        'type'          => 'match_form_fields',
                    );

                    $fields[ $slug ][] = array( 'type' => 'sectionend', 'id' => 'section_' . $form['name'] );
                }
            }
        }

        $section = !$section ? array_shift( $fields ) : $fields[ $section ];

        return $section;
    }

    /**
     * Hook new type of option field
     *
     * @param array $value contains the field configs
     *
     * @return void
     */
    public function output_match_form_fields( $value ) {
    ?>
        <tr class="cfi-table-container">
            <td style="padding-left: 0; padding-top: 0;">
                <table
                    class="wp-list-table widefat fixed striped cfi-table"
                    id="<?php echo $value['plugin'] . '_' . $value['form_id']; ?>"
                    data-plugin="<?php echo $value['plugin']; ?>"
                    data-form-id="<?php echo $value['form_id']; ?>"
                    v-cloak
                >
                    <thead>
                        <tr>
                            <th class="cfi-table-wide-column"><?php _e( 'Form Field', 'wp-erp' ); ?></th>
                            <th class="cfi-table-wide-column"><?php _e( 'CRM Contact Option' ); ?></th>
                            <th class="cfi-table-narrow-column">&nbsp;</th>
                        </tr>
                    </thead>

                    <tbody v-for="(field, title) in formData.fields">
                        <tr>
                            <td>{{ title }}</td>
                            <td>{{ getCRMOptionTitle(field) }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="button button-default"
                                    v-on:click="resetMapping(field)"
                                    :disabled="isMapped(field)"
                                >
                                    <i class="dashicons dashicons-no-alt"></i>
                                </button>
                                <button type="button" class="button button-default" v-on:click="setActiveDropDown(field)">
                                    <i class="dashicons dashicons-screenoptions"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-show="field === activeDropDown">
                            <td colspan="3" class="cfi-contact-options">
                                <button
                                    type="button"
                                    class="button"
                                    v-for="(option, optionTitle) in crmOptions"
                                    v-if="!optionIsAnObject(option)"
                                    v-on:click="mapOption(field, option)"
                                    :disabled="isOptionMapped(field, option)"
                                >{{ optionTitle }}</button>

                                <span v-for="(option, options) in crmOptions" v-if="optionIsAnObject(option)">
                                    <button
                                        type="button"
                                        class="button"
                                        v-for="(childOption, childOptionTitle) in options.options"
                                        v-if="optionIsAnObject(option)"
                                        v-on:click="mapChildOption(field, option, childOption)"
                                        :disabled="isChildOptionMapped(field, option, childOption)"
                                    >{{ options.title + ' - ' + childOptionTitle }}</button>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td></td>
        </tr>
        <tr class="cfi-table-submit">
            <td>
                <button
                    type="reset"
                    class="button cfi-settings-reset"
                    data-plugin="<?php echo $value['plugin']; ?>"
                    data-form-id="<?php echo $value['form_id']; ?>"
                ><?php echo __( 'Reset', 'wp-erp' ); ?></button>
                <button
                    type="submit"
                    class="button button-primary cfi-settings-submit"
                    data-plugin="<?php echo $value['plugin']; ?>"
                    data-form-id="<?php echo $value['form_id']; ?>"
                ><?php echo __( 'Save Changes', 'wp-erp' ); ?></button>
            </td>
        </tr>
    <?php
    }

    /**
     * Ajax hook function to save the ERP Settings
     *
     * @return void prints json object
     */
    public function save_erp_settings() {
        $response = array(
            'success' => false,
            'msg' => null
        );

        $this->verify_nonce( 'erp_settings_contact_forms' );

        if ( !erp_crm_is_current_user_manager() ) {
            $response['msg'] = __( 'Unauthorized operation', 'wp-erp' );

        } if ( !empty( $_POST['plugin'] ) && !empty( $_POST['formId'] ) && !empty( $_POST['map'] ) ) {

            $required_options = $this->contact_form_integration->get_required_crm_contact_options();

            // if map contains full_name, then remove first and last names from required options
            if ( in_array( 'full_name' , $_POST['map'] ) ) {
                $index = array_search( 'first_name' , $required_options );
                unset( $required_options[ $index ] );

                $index = array_search( 'last_name' , $required_options );
                unset( $required_options[ $index ] );

                array_unshift( $required_options, 'full_name' );
            }

            $diff = array_diff( $required_options, $_POST['map'] );

            if ( !empty( $diff ) ) {
                $required_options = array_map( function ( $option ) {
                    return ucwords( str_replace( '_' , ' ', $option ) );
                }, $required_options );

                $response['msg'] = sprintf(
                    __( '%s fields are required', 'wp-erp' ),
                    implode( ', ' , $required_options )
                );

            } else {
                $settings = get_option( 'wperp_crm_contact_forms' );

                $settings[ $_POST['plugin'] ][ $_POST['formId'] ] = $_POST['map'];

                update_option( 'wperp_crm_contact_forms', $settings );

                $response = array(
                    'success' => true,
                    'msg' => __( 'Settings saved successfully', 'wp-erp' )
                );
            }


        } else if ( empty( $_POST['forms'] ) ) {
            $response['msg'] = __( 'No settings data found', 'wp-erp' );
        }

        wp_send_json( $response );
    }

    /**
     * Ajax hook function to reset ERP Settings for a form
     *
     * @return void prints json object
     */
    public function reset_erp_settings() {
        $response = array(
            'success' => false,
            'msg' => null
        );

        $this->verify_nonce( 'erp_settings_contact_forms' );

        if ( !erp_crm_is_current_user_manager() ) {
            $response['msg'] = __( 'Unauthorized operation', 'wp-erp' );

        } else if ( !empty( $_POST['plugin'] ) && !empty( $_POST['formId'] ) ) {
            $settings = get_option( 'wperp_crm_contact_forms' );

            if ( !empty( $settings[ $_POST['plugin'] ][ $_POST['formId'] ] ) ) {
                $map = $settings[ $_POST['plugin'] ][ $_POST['formId'] ];

                unset( $settings[ $_POST['plugin'] ][ $_POST['formId'] ] );

                update_option( 'wperp_crm_contact_forms', $settings );

                // map the $map array to null values
                $map = array_map( function () {
                    return null;
                }, $map);

                $response = array(
                    'success' => true,
                    'msg' => __( 'Settings reset successfully', 'wp-erp' ),
                    'map' => $map
                );
            }
        }

        wp_send_json( $response );
    }

    /**
     * Get all Contact Form 7 forms and their fields
     *
     * @return array
     */
    public function crm_get_contact_form_7_forms() {
        $forms = array();

        $args = array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        );

        $cf7_query = new WP_Query( $args );

        if ( !$cf7_query->have_posts() ) {
            return $forms;

        } else {
            while ( $cf7_query->have_posts() ) {
                $cf7_query->the_post();
                global $post;


                $cf7 = WPCF7_ContactForm::get_instance( $post->ID );

                $saved_settings = get_option( 'wperp_crm_contact_forms', '' );

                $forms[ $post->ID ] = array(
                    'name' => $post->post_name,
                    'title' => $post->post_title,
                    'fields' => array()
                );

                foreach ( $cf7->collect_mail_tags() as $tag ) {
                    $forms[ $post->ID ]['fields'][ $tag ] = '[' . $tag . ']';

                    if ( !empty( $saved_settings['contact_form_7'][ $post->ID ][ $tag ] ) ) {
                        $crm_option = $saved_settings['contact_form_7'][ $post->ID ][ $tag ];
                    } else {
                        $crm_option = '';
                    }

                    $forms[ $post->ID ]['map'][ $tag ] = !empty( $crm_option ) ? $crm_option : '';
                }
            }
        }

        return $forms;
    }

    /**
     * Get all Ninja Forms forms and their fields
     *
     * @return array
     */
    public function crm_get_ninja_forms_forms() {
        $forms = array();
        $saved_settings = get_option( 'wperp_crm_contact_forms', '' );

        $nf = Ninja_forms();

        if ( !nf_is_freemius_on() ) {
            /* Support for non-freemius version */
            $form_ids = $nf->forms()->get_all();

            if ( !empty( $form_ids ) ) {
                foreach ( $form_ids as $form_id ) {
                    $form = $nf->form( $form_id );

                    $forms[ $form_id ] = array(
                        'name' => $form_id,
                        'title' => $form->settings['form_title'],
                        'fields' => array()
                    );

                    foreach ( $form->fields as $i => $field ) {
                        $forms[ $form_id ]['fields'][ $field['id'] ] = $field['data']['label'];

                        if ( !empty( $saved_settings['ninja_forms'][ $form_id ][ $field['id'] ] ) ) {
                            $crm_option = $saved_settings['ninja_forms'][ $form_id ][ $field['id'] ];
                        } else {
                            $crm_option = '';
                        }

                        $forms[ $form_id ]['map'][ $field['id'] ] = !empty( $crm_option ) ? $crm_option : '';
                    }
                }
            }

        } else {
            /* Support for freemius version */
            $nf_forms = $nf->form()->get_forms();

            foreach ( $nf_forms as $i => $nform ) {
                $form_id = $nform->get_id();
                $form_settings = $nform->get_settings();
                $fields = $nf->form( $form_id )->get_fields();

                $forms[ $form_id ] = array(
                    'name' => $form_id,
                    'title' => $form_settings['title'],
                    'fields' => array()
                );

                foreach ( $fields as $i => $field ) {
                    $field_id = $field->get_id();
                    $field_settings = $field->get_settings();

                    $forms[ $form_id ]['fields'][ $field_id ] = $field_settings['label'];

                    if ( !empty( $saved_settings['ninja_forms'][ $form_id ][ $field_id ] ) ) {
                        $crm_option = $saved_settings['ninja_forms'][ $form_id ][ $field_id ];
                    } else {
                        $crm_option = '';
                    }

                    $forms[ $form_id ]['map'][ $field_id ] = !empty( $crm_option ) ? $crm_option : '';

                }
            }
        }

        return $forms;
    }

}
