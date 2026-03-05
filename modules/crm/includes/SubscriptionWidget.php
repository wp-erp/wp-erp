<?php

namespace WeDevs\ERP\CRM;

/**
 * Widget definition for ERP Subscription form
 */
class SubscriptionWidget extends \WP_Widget {

    /**
     * Class constructor
     *
     * @since 1.1.17
     *
     * @return void
     */
    public function __construct() {
        $widget_ops = [
            'classname'   => 'erp-subscription-from-widget',
            'description' => __( 'Add a newsletter subscription form', 'erp' ),
        ];

        parent::__construct( 'erp-subscription-from-widget', __( 'ERP Subscription Form', 'erp' ), $widget_ops );
    }

    /**
     * Generates the admin panel widget form
     *
     * @since 1.1.17
     *
     * @param array $instance
     *
     * @return void
     */
    public function form( $instance ) {
        $args = [
            'number'  => -1,
            'orderby' => 'name',
            'order'   => 'ASC',
        ];

        $erp_contact_groups = erp_crm_get_contact_groups( $args );

        //Defaults
        $defaults = [
            'title'                 => __( 'Subscribe to Newsletter', 'erp' ),
            'description'           => __( 'Subscribe to our newsletter and we will inform you about our newest project.', 'erp' ),
            'contact_groups'        => [],
            'life_stage'            => erp_get_option( 'life_stage', 'erp_settings_erp-crm_contacts', 'subscriber' ),
            'show_name_fields'      => 'no',
            'button_label'          => __( 'Subscribe', 'erp' ),
        ];

        $instance           = wp_parse_args( (array) $instance, $defaults );

        $title              = sanitize_text_field( $instance['title'] );
        $description        = sanitize_text_field( $instance['description'] );
        $selected_groups    = $instance['contact_groups'];
        $life_stage         = $instance['life_stage'];
        $show_name_fields   = $instance['show_name_fields'];
        $button_label       = $instance['button_label']; ?>
        <p>
            <label>
                <?php esc_attr_e( 'Title', 'erp' ); ?>:
                <input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </label>
        </p>

        <p>
            <label>
                <?php esc_attr_e( 'Description', 'erp' ); ?>:
                <textarea class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>" rows="3"><?php echo esc_attr( $description ); ?></textarea>
            </label>
        </p>

        <label><?php esc_attr_e( 'Contact Groups', 'erp' ); ?>:</label>
        <ul style="margin-top: 5px;">
            <?php foreach ( $erp_contact_groups as $group ) { ?>
                <li>
                    <label>
                        <input type="checkbox" class="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'contact_groups[]' ) ); ?>" value="<?php echo esc_attr( $group->id ); ?>" <?php echo in_array( $group->id, $selected_groups ) ? 'checked' : ''; ?> >
                        <?php echo esc_attr( $group->name ); ?>
                    </label>
                </li>
            <?php } ?>
        </ul>

        <p>
            <?php esc_attr_e( 'Show name fields', 'erp' ); ?>:<br>
            <label> <input type="radio" class="radio" name="<?php echo esc_attr( $this->get_field_name( 'show_name_fields' ) ); ?>" value="no" <?php echo ( 'no' === $show_name_fields ) ? 'checked' : ''; ?>>
                <?php esc_attr_e( 'No', 'erp' ); ?>
            </label><br>

            <label> <input type="radio" class="radio" name="<?php echo esc_attr( $this->get_field_name( 'show_name_fields' ) ); ?>" value="first_last_names" <?php echo ( 'first_last_names' === $show_name_fields ) ? 'checked' : ''; ?>>
                <?php esc_attr_e( 'First and Last names', 'erp' ); ?>
            </label><br>

            <label> <input type="radio" class="radio" name="<?php echo esc_attr( $this->get_field_name( 'show_name_fields' ) ); ?>" value="full_name" <?php echo ( 'full_name' === $show_name_fields ) ? 'checked' : ''; ?>>
                <?php esc_attr_e( 'Full Name', 'erp' ); ?>
            </label>
        </p>

        <p>
            <label>
                <?php esc_attr_e( 'Life Stage', 'erp' ); ?>:<br>
                <select name="<?php echo esc_attr( $this->get_field_name( 'life_stage' ) ); ?>" class="widefat">
                    <?php echo wp_kses( erp_crm_get_life_stages_dropdown( [], $life_stage ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] ); ?>
                </select>
            </label>
        </p>

        <p>
            <label>
                <?php esc_attr_e( 'Button Label', 'erp' ); ?>:
                <input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'button_label' ) ); ?>" type="text" value="<?php echo esc_attr( $button_label ); ?>">
            </label>
        </p>
        <?php
    }

    /**
     * The update handler for this widget
     *
     * @since 1.1.17
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title']            = sanitize_text_field( $new_instance['title'] );
        $instance['description']      = sanitize_text_field( $new_instance['description'] );
        $instance['contact_groups']   = ! empty( $new_instance['contact_groups'] ) ? $new_instance['contact_groups'] : [];
        $instance['life_stage']       = ! empty( $new_instance['life_stage'] ) ? $new_instance['life_stage'] : 'subscriber';
        $instance['show_name_fields'] = ! empty( $new_instance['show_name_fields'] ) ? $new_instance['show_name_fields'] : 'no';
        $instance['button_label']     = sanitize_text_field( $new_instance['button_label'] );

        return $new_instance;
    }

    /**
     * Frontend renderer method
     *
     * @since 1.1.17
     *
     * @param array $args
     * @param array $instance
     *
     * @return void
     */
    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

        echo wp_kses_post( $args['before_widget'] );

        if ( $title ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        if ( ! empty( $instance['description'] ) ) {
            echo wp_kses_post( wpautop( $instance['description'], true ) );
        }

        $default_life_stage = erp_get_option( 'life_stage', 'erp_settings_erp-crm_contacts', 'subscriber' );

        $attrs = [
            'group'       => ! empty( $instance['contact_groups'] ) ? $instance['contact_groups'] : [],
            'life_stage'  => ! empty( $instance['life_stage'] ) ? $instance['life_stage'] : $default_life_stage,
            'button'      => ! empty( $instance['button_label'] ) ? $instance['button_label'] : __( 'Subscribe', 'erp' ),
        ];

        if ( 'first_last_names' === $instance['show_name_fields'] ) {
            $attrs['first_name'] = __( 'First Name', 'erp' );
            $attrs['last_name']  = __( 'Last Name', 'erp' );
        } elseif ( 'full_name' === $instance['show_name_fields'] ) {
            $attrs['full_name'] = __( 'Full Name', 'erp' );
        }

        echo wp_kses( Subscription::instance()->shortcode( $attrs ), [
            'form'   => [
                'class' => [],
            ],
            'label'  => [
                'class' => [],
            ],
            'input'  => [
                'type'        => [],
                'class'       => [],
                'name'        => [],
                'placeholder' => [],
                'value'       => [],
            ],
            'button' => [
                'type' => [],
            ],
            'span'   => [
                'class' => [],
            ],
            'div'    => [
                'class' => [],
            ],
        ] );

        echo wp_kses_post( $args['after_widget'] );
    }
}
