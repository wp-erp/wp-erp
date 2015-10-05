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
    echo '<label for="' . esc_attr( $field_id ) . '">' . wp_kses_post( $label ) . $req . '</label>';
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
            if ( ! empty( $value ) ) {
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
        echo '<' . $field['tag'] . ' class="erp-form-field ' . esc_attr( $field['name'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
    }

    if ( ! empty( $field['label'] ) ) {
        erp_html_form_label( $field['label'], $field_id, $field['required'] );
    }

    if ( ! empty( $field['addon'] ) ) {
        echo '<div class="input-group">';

        if ( $field['addon_pos'] == 'before' ) {
            echo '<span class="input-group-addon">' . $field['addon'] . '</span>';
        }
    }

    switch ( $field['type'] ) {
        case 'text':
        case 'email':
        case 'number':
        case 'url':
            echo '<input type="' . $field['type'] . '" value="' . esc_attr( $field['value'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
            break;

        case 'select':
            if ( $field['options'] ) {
                echo '<select ' . implode( ' ', $custom_attributes ) . '>';
                foreach ($field['options'] as $key => $value) {
                    printf( "<option value='%s'%s>%s</option>\n", $key, selected( $field['value'], $key, false ), $value );
                }
                echo '</select>';
            }
            break;

        case 'textarea':
            echo '<textarea ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea>';
            break;

        case 'checkbox':
            //echo '<input type="hidden" value="off" name="' . $field['name'] . '" />';
            echo '<input type="checkbox" '.checked( $field['value'], 'on', false ).' value="on" ' . implode( ' ', $custom_attributes ) . ' />';
            break;

        case 'radio':
            if ( $field['options'] ) {
                foreach ( $field['options'] as $key => $value) {
                    echo '<input type="radio" '.checked( $field['value'], $key, false ).' value="'.$key.'" ' . implode( ' ', $custom_attributes ) . ' />'. $value . '&nbsp;';

                }
            }
            break;

        default:
            # code...
            break;
    }

    if ( ! empty( $field['addon'] ) ) {

        if ( $field['addon_pos'] == 'after' ) {
            echo '<span class="input-group-addon">' . $field['addon'] . '</span>';
        }

        echo '</div>';
    }

    erp_html_form_help( $field['help'] );

    // closing tag
    if ( ! empty( $field['tag'] ) ) {
        echo '</' . $field['tag'] . '>';
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
        <p><strong><?php echo $text; ?></strong></p>
    </div>
    <?php
}