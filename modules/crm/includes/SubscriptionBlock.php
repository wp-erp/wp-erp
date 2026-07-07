<?php

namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * ERP Subscription Form Gutenberg Block
 *
 * Registers a server-rendered block that wraps the existing
 * `erp_subscription_form` shortcode / Subscription::subscription_form().
 *
 * @since 1.17.6
 */
class SubscriptionBlock {

    use Hooker;

    /**
     * Block name
     *
     * @var string
     */
    const BLOCK_NAME = 'erp/subscription-form';

    /**
     * Class constructor
     *
     * @since 1.17.6
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'init', 'register_block' );
    }

    /**
     * Register the block type and its editor script
     *
     * @since 1.17.6
     *
     * @return void
     */
    public function register_block() {
        // Block editor may be unavailable on very old WP.
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        $this->register_editor_script();

        register_block_type( self::BLOCK_NAME, [
            'editor_script'   => 'erp-subscription-block',
            'render_callback' => [ $this, 'render' ],
            'attributes'      => [
                'group' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'button' => [
                    'type'    => 'string',
                    'default' => __( 'Subscribe', 'erp' ),
                ],
                'email' => [
                    'type'    => 'string',
                    'default' => __( 'Email', 'erp' ),
                ],
                'first_name' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'last_name' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'full_name' => [
                    'type'    => 'string',
                    'default' => '',
                ],
            ],
        ] );
    }

    /**
     * Register the inline editor script (no webpack build required)
     *
     * @since 1.17.6
     *
     * @return void
     */
    private function register_editor_script() {
        // Register an empty handle we can attach inline JS to.
        wp_register_script(
            'erp-subscription-block',
            '',
            [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ],
            WPERP_VERSION,
            true
        );

        $groups = [];

        foreach ( erp_crm_get_contact_groups_list() as $id => $name ) {
            $groups[] = [
                'value' => (string) $id,
                'label' => $name,
            ];
        }

        wp_localize_script( 'erp-subscription-block', 'erpSubscriptionBlock', [
            'blockName' => self::BLOCK_NAME,
            'groups'    => $groups,
        ] );

        wp_add_inline_script( 'erp-subscription-block', $this->get_editor_js() );
    }

    /**
     * Server-side render callback
     *
     * @since 1.17.6
     *
     * @param array $attributes Block attributes
     *
     * @return string
     */
    public function render( $attributes ) {
        $group = isset( $attributes['group'] ) ? trim( $attributes['group'] ) : '';

        // No group selected yet — show a friendly hint in the editor preview,
        // and render nothing on the frontend.
        if ( '' === $group ) {
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return '<div class="erp-subscription-block-placeholder" style="padding:16px;border:1px dashed #c3c4c7;color:#646970;text-align:center;">'
                    . esc_html__( 'Select a contact group in the block settings to display the subscription form.', 'erp' )
                    . '</div>';
            }

            return '';
        }

        // Map block attributes to the shortcode's attribute names. Passing
        // through shortcode() (rather than subscription_form() directly)
        // guarantees every arg the template reads is populated with a default,
        // avoiding PHP "undefined array key" warnings.
        $shortcode_attrs = [ 'group' => $group ];

        if ( ! empty( $attributes['button'] ) ) {
            $shortcode_attrs['button'] = $attributes['button'];
        }

        if ( ! empty( $attributes['email'] ) ) {
            $shortcode_attrs['email'] = $attributes['email'];
        }

        if ( ! empty( $attributes['full_name'] ) ) {
            $shortcode_attrs['full_name'] = $attributes['full_name'];
        }

        if ( ! empty( $attributes['first_name'] ) ) {
            $shortcode_attrs['first_name'] = $attributes['first_name'];
        }

        if ( ! empty( $attributes['last_name'] ) ) {
            $shortcode_attrs['last_name'] = $attributes['last_name'];
        }

        // The subscription form depends on the erp-subscription-form script
        // (ajaxurl + nonce) and stylesheet. These are normally enqueued on
        // wp_enqueue_scripts; enqueue here too so the editor REST preview and
        // late-rendered blocks are styled and functional.
        Subscription::instance()->wp_enqueue_scripts();

        // shortcode() returns the rendered HTML string (or a WP_Error object
        // from subscription_form() on bad input, which we discard).
        $output = Subscription::instance()->shortcode( $shortcode_attrs );

        if ( ! is_string( $output ) ) {
            $output = '';
        }

        return $output;
    }

    /**
     * Editor JavaScript (registers the block in the editor)
     *
     * Uses the global wp.* runtime — no build step needed.
     *
     * @since 1.17.6
     *
     * @return string
     */
    private function get_editor_js() {
        return <<<'JS'
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var ServerSideRender = serverSideRender;

    var data = window.erpSubscriptionBlock || { blockName: 'erp/subscription-form', groups: [] };

    var groupOptions = [ { value: '', label: __( '— Select a group —', 'erp' ) } ].concat( data.groups );

    blocks.registerBlockType( data.blockName, {
        apiVersion: 2,
        title: __( 'ERP Subscription Form', 'erp' ),
        description: __( 'Display a WP ERP CRM contact subscription form.', 'erp' ),
        icon: 'email-alt',
        category: 'widgets',
        keywords: [ __( 'erp', 'erp' ), __( 'crm', 'erp' ), __( 'subscribe', 'erp' ), __( 'newsletter', 'erp' ) ],
        attributes: {
            group:      { type: 'string', default: '' },
            button:     { type: 'string', default: __( 'Subscribe', 'erp' ) },
            email:      { type: 'string', default: __( 'Email', 'erp' ) },
            first_name: { type: 'string', default: '' },
            last_name:  { type: 'string', default: '' },
            full_name:  { type: 'string', default: '' }
        },

        edit: function ( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            var inspector = el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    { title: __( 'Form Settings', 'erp' ), initialOpen: true },
                    el( SelectControl, {
                        label: __( 'Contact Group', 'erp' ),
                        value: attributes.group,
                        options: groupOptions,
                        onChange: function ( value ) { setAttributes( { group: value } ); },
                        help: __( 'Subscribers will be added to this group.', 'erp' )
                    } ),
                    el( TextControl, {
                        label: __( 'Button Label', 'erp' ),
                        value: attributes.button,
                        onChange: function ( value ) { setAttributes( { button: value } ); }
                    } ),
                    el( TextControl, {
                        label: __( 'Email Field Label', 'erp' ),
                        value: attributes.email,
                        onChange: function ( value ) { setAttributes( { email: value } ); }
                    } )
                ),
                el(
                    PanelBody,
                    { title: __( 'Optional Name Fields', 'erp' ), initialOpen: false },
                    el( TextControl, {
                        label: __( 'Full Name Label', 'erp' ),
                        value: attributes.full_name,
                        onChange: function ( value ) { setAttributes( { full_name: value } ); },
                        help: __( 'Leave empty to hide. Overrides first/last name fields.', 'erp' )
                    } ),
                    el( TextControl, {
                        label: __( 'First Name Label', 'erp' ),
                        value: attributes.first_name,
                        onChange: function ( value ) { setAttributes( { first_name: value } ); }
                    } ),
                    el( TextControl, {
                        label: __( 'Last Name Label', 'erp' ),
                        value: attributes.last_name,
                        onChange: function ( value ) { setAttributes( { last_name: value } ); }
                    } )
                )
            );

            var preview = el( ServerSideRender, {
                block: data.blockName,
                attributes: attributes
            } );

            return el( 'div', blockProps, inspector, preview );
        },

        save: function () {
            // Server-rendered — nothing saved to post content.
            return null;
        }
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n,
    window.wp.serverSideRender
);
JS;
    }
}
