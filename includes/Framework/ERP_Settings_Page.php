<?php

namespace WeDevs\ERP\Framework;

use WeDevs\ERP\Settings\Helpers;

/**
 * Erp Settings page main class
 *
 * @deprecated 1.9.0
 */
class ERP_Settings_Page {

     /**
     * Page ID
     *
     * @var string
     */
    public $id = '';

    /**
     * Page label
     *
     * @var string
     */
    public $label = '';

    /**
     * Single options update or multiple
     *
     * @var bool
     */
    public $single_option = false;

    /**
     * Section fields
     *
     * @var array
     */
    public $section_fields = [];

    /**
     * Icon For Section
     *
     * @var string
     */
    public $icon = "";

    /**
     * Extra variables
     *
     * @return array
     */
    public $extra = [];

    /**
     * Get id
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get saved option id
     *
     * @return string
     */
    public function get_option_id() {
        $option_id = 'erp_settings_' . $this->id;

        if ( $sections = $this->get_sections() ) {
            if ( isset( $_REQUEST['section'] ) && array_key_exists( sanitize_key( $_REQUEST['section'] ), $sections ) ) {
                $current_section = sanitize_text_field( wp_unslash( $_REQUEST['section'] ) );
                $option_id       = 'erp_settings_' . $this->id . '_' . $current_section;
            } else {
                $option_id = 'erp_settings_' . $this->id . '_' . strtolower( reset( $sections ) ); // section's first element
            }
        }

        return $option_id;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function get_label() {
        return $this->label;
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {
        return [];
    }

    public function save( $section = false ) {
        global $current_class;

        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
            $from_sections = false;

            if ( isset( $this->sections ) && is_array( $this->sections ) && count( $this->sections ) ) {
                $options       = $this->get_section_fields( $section );
                $from_sections = true;
            } else {
                $options = $this->get_settings();
            }

            // Modify options data for some sub sections
            $sub_sub_section = isset( $_POST['sub_sub_section' ] ) ? sanitize_text_field( wp_unslash( $_POST['sub_sub_section' ] ) ) : null;

            if ( ! empty ( $sub_sub_section ) ) {
                $options = $options[ $sub_sub_section ];
            }

            // Options to update will be stored here
            $update_options = [];

            // Loop options and get values to save
            foreach ( $options as $value ) {
                if ( ! isset( $value['id'] ) ) {
                    continue;
                }

                $option_value = $this->parse_option_value( $value );

                if ( ! is_null( $option_value ) ) {
                    // Check if option is an array
                    if ( strstr( $value['id'], '[' ) ) {
                        parse_str( $value['id'], $option_array );

                        // Option name is first key
                        $option_name = current( array_keys( $option_array ) );

                        // Get old option value
                        if ( ! isset( $update_options[ $option_name ] ) ) {
                            $update_options[ $option_name ] = get_option( $option_name, [] );
                        }

                        if ( ! is_array( $update_options[ $option_name ] ) ) {
                            $update_options[ $option_name ] = [];
                        }

                        // Set keys and value
                        $key = key( $option_array[ $option_name ] );

                        $update_options[ $option_name ][ $key ] = $option_value;

                    // Single value
                    } else {
                        $update_options[ $value['id'] ] = $option_value;
                    }
                }

                // Custom handling
                do_action( 'erp_update_option', $value );
            }

            // finally, update the option
            if ( $update_options ) {
                if ( $this->single_option ) {
                    foreach ( $update_options as $name => $value ) {
                        update_option( $name, $value );
                    }
                } else {
                    update_option( $this->get_option_id(), $update_options );
                }
            }

            do_action( 'erp_after_save_settings' );
        }
    }

    public function parse_option_value( $value ) {
        $type         = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';
        $option_value = null;

        switch ( $type ) {

            // Standard types
            case 'checkbox':

                if ( ! empty ( $_POST[ $value['id'] ] ) ) {
                    $option_value = 'yes';
                } else {
                    $option_value = 'no';
                }

                break;

            case 'textarea':
            case 'wysiwyg':

                if ( isset( $_POST[$value['id']] ) ) {
                    $option_value = wp_kses_post( wp_unslash( $_POST[ $value['id'] ] ) );
                } else {
                    $option_value = '';
                }

                break;

            case 'multicheck':

                if ( isset( $_POST[$value['id']] ) ) {
                    $option_value = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $value['id'] ] ) );
                } else {
                    $option_value = [];
                }

                break;

            case 'text':
            case 'email':
            case 'number':
            case 'select':
            case 'color':
            case 'password':
            case 'single_select_page':
            case 'radio':
            case 'hidden':
            case 'hidden-fixed':
                $option_value = '';
                if ( isset( $_POST[ $value['id'] ] ) ) {
                    $option_value = sanitize_text_field( wp_unslash( $_POST[ $value['id'] ] ) );
                }
                break;

            case 'image':
                $option_value = '';

                if ( ! isset( $_FILES[ $value['id'] ] ) ) {
                    break;
                }

                $upload = [
                    'name'        => isset( $_FILES[ $value['id'] ]['name'] ) ? sanitize_file_name( wp_unslash( $_FILES[ $value['id'] ]['name'] ) ) : '',
                    'type'        => isset( $_FILES[ $value['id'] ]['type'] ) ? sanitize_mime_type( wp_unslash( $_FILES[ $value['id'] ]['type'] ) ) : '',
                    'tmp_name'    => isset( $_FILES[ $value['id'] ]['tmp_name'] ) ? sanitize_url( wp_unslash( $_FILES[ $value['id'] ]['tmp_name'] ) ) : '',
                    'error'       => isset( $_FILES[ $value['id'] ]['error'] ) ? sanitize_text_field( wp_unslash( $_FILES[ $value['id'] ]['error'] ) ) : '',
                    'size'        => isset( $_FILES[ $value['id'] ]['size'] ) ? sanitize_text_field( wp_unslash( $_FILES[ $value['id'] ]['size'] ) ) : '',
                    'post_status' => 'erp_hr_rec',
                ];

                $uploader = new \WeDevs\ERP\Uploader();
                $uploaded = $uploader->handle_upload( $upload );

                if ( $uploaded['success'] && ! empty( $uploaded['attach_id'] ) ) {
                    $option_value = $uploaded['attach_id'];
                }

                break;

            // Special types
            case 'multiselect':
                // Get countries array
                $selected_countries = [];
                if ( isset( $_POST[ $value['id'] ] ) ) {
                    $selected_countries = array_map( 'sanitize_text_field', wp_unslash( (array) $_POST[ $value['id'] ] ) );
                }

                $option_value = $selected_countries;
                break;

            // Custom handling
            default:
                do_action( 'erp_update_option_' . $type, $value );
                break;

        }

        return $option_value;
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        return [];
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function get_section_fields( $section = false ) {
        return [];
    }

    public function get_section_field_items() {
        return $this->section_fields;
    }

    public function output( $section = false ) {
        $fields         = $this->get_settings();
        $sections       = $this->get_sections();
        $section_fields = $this->get_section_fields( $section );
        $query_arg      = Helpers::get_current_tab_and_section();

        if ( count( $sections ) && $query_arg['subtab'] ) {
            if ( ! array_key_exists( $query_arg['subtab'], $sections ) ) {
                return;
            }

            $fields = $section_fields;
        }

        if ( $fields ) {
            $this->section_fields = $fields;
            $this->output_fields( $fields );
        }
    }

    public function output_fields( $fields ) {
        $defaults = [
            'id'                => '',
            'title'             => '',
            'class'             => '',
            'css'               => '',
            'default'           => '',
            'desc'              => '',
            'tooltip'           => false,
            'custom_attributes' => [],
        ];

        foreach ( $fields as $field ) {
            if ( ! isset( $field['type'] ) ) {
                continue;
            }

            $value = wp_parse_args( $field, $defaults );

            // Custom attribute handling
            $custom_attributes = [];

            if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
                foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }

            // Description handling
            if ( $value['tooltip'] === true ) {
                $description = '';
                $tip         = $value['desc'];
            } elseif ( ! empty( $value['tooltip'] ) ) {
                $description = $value['desc'];
                $tip         = $value['tooltip'];
            } elseif ( ! empty( $value['desc'] ) ) {
                $description = $value['desc'];
                $tip         = '';
            } else {
                $description = $tip = '';
            }

            if ( $description && in_array( $value['type'], [ 'textarea', 'radio' ] ) ) {
                $description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
            } elseif ( $description && in_array( $value['type'], [ 'checkbox' ] ) ) {
                $description =  wp_kses_post( $description );
            } elseif ( $description ) {
                $description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
            }

            if ( $tip && in_array( $value['type'], [ 'checkbox' ] ) ) {
                $tip = '<p class="description">' . $tip . '</p>';
            } elseif ( $tip ) {
                $tip = '<img class="help_tip" data-tip="' . wp_kses_post( $tip ) . '" src="' . WPERP_ASSETS . '/images/help.png" height="16" width="16" />';
            }

            // Switch based on type
            switch ( $value['type'] ) {

                // Section Titles
                case 'title':
                    if ( ! empty( $value['title'] ) ) {
                        echo '<h3>' . esc_html( $value['title'] ) . '</h3>';
                    }

                    if ( ! empty( $value['desc'] ) ) {
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
                    }
                    echo '<table class="form-table">' . "\n\n";

                    if ( ! empty( $value['id'] ) ) {
                        do_action( 'erp_settings_' . sanitize_title( $value['id'] ) );
                    }
                break;

                // Section Ends
                case 'sectionend':
                    if ( ! empty( $value['id'] ) ) {
                        do_action( 'erp_settings_' . sanitize_title( $value['id'] ) . '_end' );
                    }
                    echo '</table>';

                    if ( ! empty( $value['id'] ) ) {
                        do_action( 'erp_settings_' . sanitize_title( $value['id'] ) . '_after' );
                    }
                break;

                // Standard text inputs and subtypes like 'number'
                case 'text':
                case 'email':
                case 'number':
                case 'color':
                case 'password':
                case 'hidden':

                    $type           = $value['type'];
                    $class          = '';
                    $option_value   = $this->get_option( $value['id'], $value['default'] );

                    if ( empty( $value['class'] ) ) {
                        $value['class'] = 'regular-text';
                    }

                    if ( $value['type'] == 'color' ) {
                        $type = 'text';
                        $value['class'] .= 'colorpick';
                    }

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
                            <?php if ( ! empty( $value['title_before_field'] ) ) { ?>
                                <h4 class="erp-settings-title-before-field"><?php echo esc_html( $value['title_before_field'] ); ?></h4>
                            <?php } ?>
                            <input
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="<?php echo esc_attr( $type ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                value="<?php echo esc_attr( $option_value ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
                                /> <?php echo wp_kses_post( $description ); ?>
                        </td>
                    </tr><?php
                break;

                case 'image':

                    $option_value   = (int) $this->get_option( $value['id'], 0 );
                    $image_url      = $option_value ? wp_get_attachment_url( $option_value ) : '';

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>

                        <td>
                            <div class="image-wrap<?php echo $option_value ? '' : ' erp-hide'; ?>">
                                <input type="hidden" class="erp-file-field" name="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo esc_attr( $option_value ); ?>">
                                <img class="erp-option-image" src="<?php echo esc_url( $image_url ); ?>">

                                <a class="erp-remove-image" title="<?php esc_attr_e( 'Delete this image?', 'erp' ); ?>">&times;</a>
                            </div>

                            <div class="button-area<?php echo esc_attr( $option_value ) ? ' erp-hide' : ''; ?>">
                                <a href="#" class="erp-image-upload button"><?php esc_html_e( 'Upload Image', 'erp' ); ?></a>
                                <?php echo wp_kses_post( $description ); ?>
                            </div>

                        </td>


                    </tr><?php
                break;

                // Textarea
                case 'textarea':

                    $option_value   = $this->get_option( $value['id'], $value['default'] );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
                            <?php if ( ! empty( $value['title_before_field'] ) ) { ?>
                                <h4 class="erp-settings-title-before-field"><?php echo esc_html( $value['title_before_field'] ); ?></h4>
                            <?php } ?>

                            <textarea
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
                                ><?php echo esc_textarea( $option_value ?? '' ); ?></textarea>

                                <?php echo wp_kses_post( $description ); ?>
                        </td>
                    </tr><?php
                break;

                // Textarea
                case 'wysiwyg':

                    $option_value   = $this->get_option( $value['id'], $value['default'] );
                    $editor_args    = [
                        'editor_class'  => $value['css'],
                        'textarea_rows' => 10,
                    ];

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">

                            <?php wp_editor( $option_value, $value['id'], $editor_args ); ?>

                            <?php echo wp_kses_post( $description ); ?>
                        </td>
                    </tr><?php
                break;

                // Select boxes
                case 'select':
                case 'multiselect':

                    $option_value   = $this->get_option( $value['id'], $value['default'] );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
                            <select
                                name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) {
                        echo '[]';
                    } ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
                                <?php if ( $value['type'] == 'multiselect' ) {
                        echo 'multiple="multiple"';
                    } ?>
                                >
                                <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php

                                            if ( is_array( $option_value ) ) {
                                                selected( in_array( $key, $option_value ), true );
                                            } else {
                                                selected( $option_value, $key );
                                            } ?>><?php echo esc_html( $val ); ?></option>
                                        <?php
                                    }
                                ?>
                           </select> <?php echo wp_kses_post( $description ); ?>
                        </td>
                    </tr><?php
                break;

                // Radio inputs
                case 'radio':

                    $option_value   = $this->get_option( $value['id'], $value['default'] );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
                            <fieldset>
                                <ul class="erp-settings-radio">
                                <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <li>
                                            <label><input
                                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                                value="<?php echo esc_attr( $key ); ?>"
                                                type="radio"
                                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                                <?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
                                                <?php checked( $key, $option_value ); ?>
                                                /> <?php echo esc_html( $val ); ?></label>
                                        </li>
                                        <?php
                                    }
                                ?>
                                </ul>
                                <?php echo wp_kses_post( $description ); ?>
                            </fieldset>
                        </td>
                    </tr><?php
                break;

                // multi check
                case 'multicheck':

                    $default        = is_array( $value['default'] ) ? $value['default'] : [];
                    $option_value   = $this->get_option( $value['id'], $default );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post( $tip ); ?>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
                            <fieldset>
                                <ul>
                                <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <li>
                                            <label><input
                                                name="<?php echo esc_attr( $value['id'] ); ?>[<?php echo esc_attr( $key ); ?>]"
                                                value="<?php echo esc_html( $key ); ?>"
                                                type="checkbox"
                                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                                <?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
                                                <?php checked( in_array( $key, $option_value ) ); ?>
                                                /> <?php echo esc_html( $val ); ?></label>
                                        </li>
                                        <?php
                                    }
                                ?>
                                </ul>
                            </fieldset>
                            <?php echo wp_kses_post( $description ); ?>
                        </td>
                    </tr><?php
                break;

                // Checkbox input
                case 'checkbox':

                    $option_value    = $this->get_option( $value['id'], $value['default'] );
                    $visbility_class = [];

                    if ( ! isset( $value['hide_if_checked'] ) ) {
                        $value['hide_if_checked'] = false;
                    }

                    if ( ! isset( $value['show_if_checked'] ) ) {
                        $value['show_if_checked'] = false;
                    }

                    if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes' ) {
                        $visbility_class[] = 'hidden_option';
                    }

                    if ( $value['hide_if_checked'] == 'option' ) {
                        $visbility_class[] = 'hide_options_if_checked';
                    }

                    if ( $value['show_if_checked'] == 'option' ) {
                        $visbility_class[] = 'show_options_if_checked';
                    }

                    if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
                        ?>
                            <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
                                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
                                <td class="forminp forminp-checkbox">
                                    <fieldset>
                        <?php
                    } else {
                        ?>
                            <fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
                        <?php
                    }

                    if ( ! empty( $value['title'] ) ) {
                        ?>
                            <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
                        <?php
                    }

                    ?>
                        <label for="<?php echo esc_attr( $value['id'] ); ?>">
                            <input
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="checkbox"
                                value="1"
                                <?php checked( $option_value, 'yes' ); ?>
                                <?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
                            /> <?php echo wp_kses_post( $description ); ?>
                        </label> <?php echo wp_kses_post( $tip ); ?>
                    <?php

                    if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
                        ?>
                                    </fieldset>
                                </td>
                            </tr>
                        <?php
                    } else {
                        ?>
                            </fieldset>
                        <?php
                    }
                break;

                // Image width settings
                case 'image_width':

                    $width  = $this->get_option( $value['id'] . '[width]', $value['default']['width'] );
                    $height = $this->get_option( $value['id'] . '[height]', $value['default']['height'] );
                    $crop   = checked( 1, $this->get_option( $value['id'] . '[crop]', $value['default']['crop'] ), false );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tip ); ?></th>
                        <td class="forminp image_width_settings">

                            <input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo esc_attr( $width ); ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo esc_attr( $height ); ?>" />px

                            <label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" <?php echo esc_html( $crop ); ?> /> <?php esc_html_e( 'Hard Crop?', 'erp' ); ?></label>

                            </td>
                    </tr><?php
                break;

                // Single page selects
                case 'single_select_page':

                    $args = [
                        'name'             => $value['id'],
                        'id'               => $value['id'],
                        'sort_column'      => 'menu_order',
                        'sort_order'       => 'ASC',
                        'show_option_none' => ' ',
                        'class'            => $value['class'],
                        'echo'             => false,
                        'selected'         => absint( $this->get_option( $value['id'] ) ),
                   ];

                    if ( isset( $value['args'] ) ) {
                        $args = wp_parse_args( $value['args'], $args );
                    }

                    ?><tr valign="top" class="single_select_page">
                        <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tip ); ?></th>
                        <td class="forminp">
                            <?php echo wp_kses_post( str_replace( ' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'erp' ) . "' style='" . esc_attr( $value['css'] ) . "' class='" . esc_attr( $value['class'] ) . "' id=", esc_attr( wp_dropdown_pages( $args ) ) ) ); ?> <?php echo wp_kses_post( $description ); ?>
                        </td>
                    </tr><?php
                break;

                // Default: run an action
                default:
                    do_action( 'erp_admin_field_' . $value['type'], $value );
                break;
            }
        }
    }

    /**
     * Output sections
     */
    public function output_sections() {
        global $current_section;

        $sections = $this->get_sections();

        if ( empty( $sections ) ) {
            return;
        }

        echo '<ul class="subsubsub">';

        $array_keys = array_keys( $sections );

        foreach ( $sections as $id => $label ) {
            echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=erp-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
        }

        echo '</ul><br class="clear" />';
    }

    /**
     * Get a setting from the settings API.
     *
     * @param mixed $option
     *
     * @return string
     */
    public function get_option( $option_name, $default = '' ) {
        if ( $this->single_option ) {
            $option_value = get_option( $option_name, $default );
        } else {
            $options      = get_option( $this->get_option_id(), [] );
            $option_value = isset( $options[$option_name] ) ? $options[$option_name] : $default;
        }

        if ( is_array( $option_value ) ) {
            $option_value = array_map( 'stripslashes', $option_value );
        } elseif ( ! is_null( $option_value ) ) {
            $option_value = stripslashes( $option_value );
        }

        return $option_value;
    }

    /**
     * Get settings options from the settings API.
     *
     * @param string $option_name
     * @param array  $options
     *
     * @since 1.8.6
     *
     * @return array|object options
     */
    public function get_settings_options( $option_name, $default = [] ) {
        return get_option( $option_name, $default );
    }
}
