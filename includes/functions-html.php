<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Prints help text
 *
 * @param  string  the description
 *
 * @return void
 */
function erp_html_form_help( $value = '' ) {
    if ( ! empty( $value ) ) {
        echo '<span class="description">' . wp_kses_post( $value ) . '</span>';
    }
}

/**
 * Prints a label attribute
 *
 * @param  string  label vlaue
 * @param  string  id field for label
 *
 * @return void
 */
function erp_html_form_label( $label, $field_id = '', $required = false ) {
    $req = $required ? ' <span class="required">*</span>' : '';
    echo '<label for="' . esc_attr( $field_id ) . '">' . wp_kses_post( $label ) . wp_kses_post( $req ) . '</label>';
}

/**
 * Handles an elements custom attribute
 *
 * @param  array   attributes as key/value pair
 *
 * @return array
 */
function erp_html_form_custom_attr( $attr = array(), $other_attr = array() ) {
    $custom_attributes = array();

    if ( ! empty( $attr ) && is_array( $attr ) ) {
        foreach ( $attr as $attribute => $value ) {
            if ( $value != '' ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
            }
        }
    }

    if ( ! empty( $other_attr ) && is_array( $other_attr ) ) {
        foreach ( $attr as $attribute => $value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
        }
    }

    return $custom_attributes;
}

/**
 * Prints a HTML input field
 *
 * @param  array   the args
 *
 * @return void
 */
function erp_html_form_input( $args = array() ) {
    $defaults = array(
        'placeholder'   => '',
        'required'      => false,
        'type'          => 'text',
        'class'         => '',
        'tag'           => '',
        'wrapper_class' => '',
        'label'         => '',
        'name'          => '',
        'id'            => '',
        'value'         => '',
        'help'          => '',
        'addon'         => '',
        'addon_pos'     => 'before',
        'custom_attr'   => array(),
        'options'       => array(),
    );

    $field    = wp_parse_args( $args, $defaults );
    $field_id = empty( $field['id'] ) ? $field['name'] : $field['id'];

    $field_attributes = array_merge( array(
        'name'        => $field['name'],
        'id'          => $field_id,
        'class'       => $field['class'],
        'placeholder' => $field['placeholder'],
    ), $field['custom_attr'] );

    if ( $field['required'] ) {
        $field_attributes['required'] = 'required';
    }

    $custom_attributes = erp_html_form_custom_attr( $field_attributes );

    // open tag
    if ( ! empty( $field['tag'] ) ) {
        echo '<' . esc_attr( $field['tag'] ) . ' class="erp-form-field ' . esc_attr( $field['name'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
    }

    if ( ! empty( $field['label'] ) ) {
        erp_html_form_label( $field['label'], $field_id, $field['required'] );
    }

    if ( ! empty( $field['addon'] ) ) {
        echo '<div class="input-group">';

        if ( $field['addon_pos'] == 'before' ) {
            echo '<span class="input-group-addon">' . esc_html( $field['addon'] ) . '</span>';
        }
    }

    switch ( $field['type'] ) {
        case 'text':
        case 'email':
        case 'number':
        case 'hidden':
        case 'date':
        case 'url':
            echo '<input type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' . wp_kses_post( implode( ' ', $custom_attributes ) ) . ' />';
            break;

        case 'select':
            if ( $field['options'] ) {
                echo '<select ' . wp_kses_post( implode( ' ', $custom_attributes ) ) . '>';
                foreach ($field['options'] as $key => $value) {
                    printf( "<option value='%s'%s>%s</option>\n", esc_attr( $key ), selected( $field['value'], esc_attr( $key ), false ), esc_html( $value ) );
                }
                echo '</select>';
            }
            break;

        case 'textarea':
            echo '<textarea ' . wp_kses_post( implode( ' ', $custom_attributes ) ) . '>' . esc_textarea( $field['value'] ) . '</textarea>';
            break;

        case 'wysiwyg':
            $editor_args = [
                'editor_class'  => $field['class'],
                'textarea_rows' => isset( $field['custom_attr']['rows'] ) ? $field['custom_attr']['rows'] : 10,
                'media_buttons' => isset( $field['custom_attr']['media'] ) ? $field['custom_attr']['media'] : false,
                'teeny'         => isset( $field['custom_attr']['teeny'] ) ? $field['custom_attr']['teeny'] : true,

            ];

            wp_editor( $field['value'], $field['name'], $editor_args );
            break;

        case 'checkbox':
            //echo '<input type="hidden" value="off" name="' . $field['name'] . '" />';
            echo '<span class="checkbox">';
            echo '<label for="' . esc_attr( $field_attributes['id'] ) . '">';
            echo '<input type="checkbox" '.checked( $field['value'], 'on', false ).' value="on" ' . wp_kses_post( implode( ' ', $custom_attributes ) ) . ' />';
            echo wp_kses_post( $field['help'] );
            echo '</label>';
            echo '</span>';
            break;

        case 'multicheckbox':

            echo '<span class="checkbox">';
            unset( $custom_attributes['id'] );

            foreach ( $field['options'] as $key => $value ) {
                echo '<label for="' . esc_attr( $field_attributes['id'] ) . '-' . esc_attr( $key ) .'">';
                if ( ! empty( $field['value'] ) ) {
                    if ( is_array( $field['value'] ) ) {
                        $checked = in_array( $key, $field['value'] ) ? 'checked' : '';
                    } else if ( is_string( $field['value'] ) ) {
                        $checked = in_array( $key, explode(',', $field['value'] ) ) ? 'checked' : '';
                    } else {
                        $checked = '';
                    }
                } else {
                    $checked = '';
                }

                echo '<input type="checkbox" '. esc_attr( $checked ) .' id="' . esc_attr( $field_attributes['id'] ) . '-' . esc_attr( $key ) . '" value="'. esc_attr( $key ) .'" ' . wp_kses_post( implode( ' ', $custom_attributes ) ) . ' />';
                echo '<span class="checkbox-value">' . wp_kses_post( $value ) . '</span>';
                echo '</label>';
            }
            echo '</span>';
            break;

        case 'radio':
            echo '<span class="checkbox">';
            if ( $field['options'] ) {
                foreach ( $field['options'] as $key => $value) {
                    echo '<input type="radio" '.checked( $field['value'], $key, false ).' value="'.esc_attr( $key ).'" ' . wp_kses_post( implode( ' ', $custom_attributes ) ) . ' id="'. esc_attr( $field_attributes['id'] ) . '-' . esc_attr( $key ) . '"/>'. esc_html( $value ) . '&nbsp; <br><br>';
                }
            }
             echo '</span>';
            break;

        case 'file':
            //need to enqueue   wp_enqueue_script( 'plupload-handlers' ); wp_enqueue_script( 'erp-file-upload' );
            $id         = $field['id'];
            $pick_files = $id . '-upload-pickfiles';
            $drop       = $id . '-drop-files';
            $action     = isset( $field['action'] ) ? $field['action'] : 'erp_file_upload';
            $call_back  = isset( $field['callback'] ) ? json_encode( $field['callback'] ) : json_encode([]);
            $values     = is_array( $field['value'] ) ? $field['value'] : [];
            ?>

            <div id="<?php echo esc_attr( $id ); ?>" class="erp-attachment-area">

                <div id="<?php echo esc_attr( $drop ); ?>" class="erp-drop-jon">
                    <div class="erp-attachment-upload-filelist" data-type="file">
                        <ul class="erp-attachment-list">
                            <?php
                                $uploader = new \WeDevs\ERP\Uploader();
                                foreach ( $values as $key => $attach_id ) {
                                    echo wp_kses_post( $uploader->attach_html( $attach_id, $custom_attributes ) );
                                }
                            ?>

                        </ul>
                        <div class="erp-clear"></div>

                        <div class="erp-attc-link-text"><?php esc_html_e( 'To attach, ', 'erp' ); ?> <a id="<?php echo esc_attr( $pick_files ); ?>" href="#"><?php esc_html_e( 'select files', 'erp' ); ?></a><?php esc_html_e( ' from your computer.', 'erp' ); ?></div>
                    </div>
                </div>
            </div>


            <script type="text/javascript">
                jQuery(function($) {
                    var pick_files = '<?php echo array_map( 'esc_html', wp_unslash( $pick_files ) ); ?>',
                        id         = '<?php echo esc_html( $id ); ?>',
                        drop_jone  = '<?php echo esc_html( $drop ); ?>',
                        action     = '<?php echo esc_html( $action ); ?>',
                        callback   = '<?php echo esc_html( $call_back ); ?>';

                    new ERP_Uploader( action, pick_files, id, drop_jone, 'file_upload', 'doc,docx,xls,xlsx,jpg,jpeg,gif,png,pdf,bmp,zip,rar', 1024, callback );
                });
            </script>
            <?php
            break;

        default:
            # code...
            break;
    }

    if ( ! empty( $field['addon'] ) ) {

        if ( $field['addon_pos'] == 'after' ) {
            echo '<span class="input-group-addon">' . esc_html( $field['addon'] ) . '</span>';
        }

        echo '</div>';
    }

    if ( $field['type'] != 'checkbox' ) {
        erp_html_form_help( $field['help'] );
    }

    // closing tag
    if ( ! empty( $field['tag'] ) ) {
        echo '</' . esc_html( $field['tag'] ) . '>';
    }
}

/**
 * Generate an HTML dropdown option by provided values
 *
 * @param  array   $values
 * @param  string  $selected
 *
 * @return string
 */
function erp_html_generate_dropdown( $values = array(), $selected = null ) {
    $dropdown  = '';

    if ( $values ) {
        foreach ($values as $key => $label) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $label );
        }
    }

    return $dropdown;
}

/**
 * Print notices for WordPress
 *
 * @param  string  $text
 * @param  string  $type
 *
 * @return void
 */
function erp_html_show_notice( $text, $type = 'updated' ) {
    ?>
    <div class="<?php echo esc_attr( $type ); ?>">
        <p><strong><?php echo esc_html( $text ); ?></strong></p>
    </div>
    <?php
}
