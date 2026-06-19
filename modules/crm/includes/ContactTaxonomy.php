<?php

namespace WeDevs\ERP\CRM;

class ContactTaxonomy {

    /**
     * The unique ID to use for the taxonomy type
     *
     * @since 1.3.6
     *
     * @var string
     */
    public $taxonomy = '';

    /**
     * The URL friendly slug to use for the taxonomy
     *
     * @since 1.3.6
     *
     * @var string
     */
    public $slug = '';

    /**
     * Array of taxonomy properties
     *
     * Use the custom `singular` and `plural` arguments to let this class
     * generate labels for you. Note that labels cannot be translated using
     * this method, so if you need different languages, use the `$labels`
     * array below.
     *
     * @since 1.3.6
     *
     * @var array
     */
    public $args = [];

    /**
     * Array of taxonomy labels, if you'd like to customize them completely
     *
     * @since 1.3.6
     *
     * @var array
     */
    public $labels = [];

    /**
     * Singular name of the taxonomy
     *
     * @since 1.3.6
     *
     * @var string
     */
    public $tax_singular = '';

    /**
     * Plural name of the taxonomy
     *
     * @since 1.3.6
     *
     * @var string
     */
    public $tax_plural = '';

    /**
     * Lowercase singular name of the taxonomy
     *
     * @since 1.3.6
     *
     * @var string
     */
    public $tax_singular_low = '';

    /**
     * Lowercase plural name of the taxonomy
     *
     * @since 1.3.6
     *
     * @var string
     */
    public $tax_plural_low = '';
    
    /**
     * Main constructor
     *
     * @since 1.3.6
     *
     * @param string $taxonomy
     * @param string $slug
     * @param array  $args
     * @param array  $labels
     */
    public function __construct( $taxonomy = '', $slug = '', $args = [], $labels = [] ) {

        // Bail if no taxonomy is passed
        if ( empty( $taxonomy ) ) {
            return;
        }

        /* Class Variables ***************************************************/

        // Set the taxonomy
        $this->taxonomy = sanitize_key( $taxonomy );
        $this->slug     = sanitize_text_field( $slug );
        $this->args     = $args;
        $this->labels   = $labels;

        // Label helpers
        $this->tax_singular     = $args['singular'];
        $this->tax_plural       = $args['plural'];
        $this->tax_singular_low = strtolower( $this->tax_singular );
        $this->tax_plural_low   = strtolower( $this->tax_plural   );

        // Register the taxonomy
        $this->register_taxonomy();

        // Hook into actions & filters
        $this->hooks();

        // JIT
        do_action( 'erp_crm_taxonomy', $this );
    }

    /**
     * Hook in to actions & filters
     *
     * @since 1.3.6
     */
    protected function hooks() {
        // Column styling
        add_action( 'admin_head', [ $this, 'admin_head'     ] );
        add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
    }

    /**
     * Add the administration page for this taxonomy
     *
     * @since 1.3.6
     */
    public function add_admin_page() {

        // Setup the URL
        $tax = get_taxonomy( $this->taxonomy );

        // No UI
        if ( false === $tax->show_ui ) {
            return;
        }

        // URL for the taxonomy
        $url = add_query_arg( [ 'taxonomy' => $tax->name ], 'edit-tags.php' );

        // Add sub menu page
        add_submenu_page( 'erp-sales', esc_attr( $tax->labels->menu_name ),
            esc_attr( $tax->labels->menu_name ),
            $tax->cap->manage_terms,
            $url );
        // Hook into early actions to load custom CSS and our init handler.
        add_action( 'erp-sales', [ $this, 'admin_load' ] );
        add_action( 'load-edit-tags.php', [ $this, 'admin_load' ] );
        add_action( 'load-term.php', [ $this, 'admin_menu_highlight' ] );
        add_action( 'load-edit-tags.php', [ $this, 'admin_menu_highlight' ] );
    }

    /**
     * This tells WordPress to highlight the "parent" menu item when viewing a
     * this taxonomy.
     *
     * @since 1.3.6
     *
     * @global string $plugin_page
     */
    public function admin_menu_highlight() {
        global $plugin_page;

        if ( isset( $_GET['taxonomy'] ) && ( sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) === $this->taxonomy ) ) {
            $plugin_page = 'erp-sales';
        }
    }

    /**
     * Filter the body class
     *
     * @since 1.3.6
     */
    public function admin_load() {
        add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );
    }

    /**
     * Add a class for this taxonomy
     *
     * @since 1.3.6
     *
     * @param string $classes
     *
     * @return string
     */
    public function admin_body_class( $classes = '' ) {

        // Add a body class for this taxonomy if it's currently selected
        if ( isset( $_GET[ $this->taxonomy ] ) ) {
            $classes .= " tax-{$this->taxonomy}";
        }

        // Return maybe modified class
        return $classes;
    }

    /**
     * Stylize custom columns
     *
     * @since 1.3.6
     */
    public function admin_head() {

        // Compile the style
        $style = "
			.column-{$this->taxonomy} {
				width: 10%;
			}";

        // Add inline style
       // wp_add_inline_style( 'erp', $style );
    }

    /** Post Type *************************************************************/

    /**
     * Register the taxonomy
     *
     * @since 1.3.6
     */
    protected function register_taxonomy() {
        register_taxonomy(
            $this->taxonomy,
            'people',
            $this->parse_options()
        );
    }

    /**
     * Parse taxonomy labels
     *
     * @since 1.3.6
     *
     * @return array
     */
    protected function parse_labels() {
        return wp_parse_args( $this->labels, [
            'menu_name'                  => $this->tax_plural,
            'name'                       => $this->tax_plural,
            'singular_name'              => $this->tax_singular,
            'search_items'               => sprintf( __( 'Search %s', 'erp' ), $this->tax_plural ),
            'popular_items'              => sprintf( __( 'Popular %s', 'erp' ), $this->tax_plural ),
            'all_items'                  => sprintf( __( 'All %s', 'erp' ), $this->tax_plural ),
            'parent_item'                => sprintf( __( 'Parent %s', 'erp' ), $this->tax_singular ),
            'parent_item_colon'          => sprintf( __( 'Parent %s:', 'erp' ), $this->tax_singular ),
            'edit_item'                  => sprintf( __( 'Edit %s', 'erp' ), $this->tax_singular ),
            'view_item'                  => sprintf( __( 'View %s', 'erp' ), $this->tax_singular ),
            'update_item'                => sprintf( __( 'Update %s', 'erp' ), $this->tax_singular ),
            'add_new_item'               => sprintf( __( 'Add New %s', 'erp' ), $this->tax_singular ),
            'new_item_name'              => sprintf( __( 'New %s Name', 'erp' ), $this->tax_singular ),
            'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'erp' ), $this->tax_plural_low ),
            'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'erp' ), $this->tax_plural_low ),
            'choose_from_most_used'      => sprintf( __( 'Choose from most used %s', 'erp' ), $this->tax_plural_low ),
            'not_found'                  => sprintf( __( 'No %s found', 'erp' ), $this->tax_plural_low ),
            'no_item'                    => sprintf( __( 'No %s', 'erp' ), $this->tax_singular ),
            'no_items'                   => sprintf( __( 'No %s', 'erp' ), $this->tax_plural_low ),
        ] );
    }

    /**
     * Parse taxonomy options
     *
     * @since 1.3.6
     *
     * @return array
     */
    protected function parse_options() {
        return wp_parse_args( $this->args, [
            // Core
            'hierarchical' => true,
            'public'       => false,
            'show_ui'      => true,
            'meta_box_cb'  => '',
            'labels'       => $this->parse_labels(),
            'rewrite'      => [
                'with_front'   => false,
                'slug'         => $this->slug,
                'hierarchical' => true,
            ],
            'capabilities' => [
                'manage_terms' => 'erp_crm_edit_contact',
                'edit_terms'   => 'erp_crm_edit_contact',
                'delete_terms' => 'erp_crm_edit_contact',
                'assign_terms' => 'erp_crm_edit_contact',
            ],

            // @see _update_post_term_count()
            'update_count_callback' => [ $this, 'update_term_count' ],
        ] );
    }

    /**
     * Update the term count for a user and taxonomy
     *
     * @since 1.3.6
     *
     * @param $terms
     * @param $taxonomy
     */
    public function update_term_count( $terms = [], $taxonomy = '' ) {
        // Fallback to this taxonomy
        if ( empty( $taxonomy ) ) {
            $taxonomy = $this->taxonomy;
        }
        // Update counts
        _update_generic_term_count( $terms, $taxonomy );
    }

    /** Bulk Edit *************************************************************/

    /**
     * Add custom bulk actions
     *
     * @since 1.0.0
     *
     * @param array $actions
     *
     * @return array
     */
    public function bulk_actions( $actions = [] ) {

        // Get taxonomy & terms
        $tax   = get_taxonomy( $this->taxonomy );
        $terms = get_terms( $this->taxonomy );

        // Add to bulk actions array
        if ( ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $actions[ "add-{$term->slug}-{$this->taxonomy}"    ] = sprintf( esc_html__( 'Add to %s %s', 'erp' ), $term->name, $tax->labels->singular_name );
                $actions[ "remove-{$term->slug}-{$this->taxonomy}" ] = sprintf( esc_html__( 'Remove from %s %s', 'erp' ), $term->name, $tax->labels->singular_name );
            }
        }

        // Return actions, maybe with our bulks added
        return $actions;
    }

    /**
     * Group add/remove options together for improved UX
     *
     * @since 1.3.6
     *
     * @param array $actions
     *
     * @return array
     */
    public function bulk_actions_sort( $actions = [] ) {

        // Actions array
        $old_actions = $add_actions = $rem_actions = [];

        // Loop through and separate out actions
        foreach ( $actions as $key => $name ) {

            // Add
            if ( 0 === strpos( $key, 'add-' ) ) {
                $add_actions[ $key ] = $name;

            // Remove
            } elseif ( 0 === strpos( $key, 'remove-' ) ) {
                $rem_actions[ $key ] = $name;

            // Old
            } else {
                $old_actions[ $key ] = $name;
            }
        }

        $new = array_merge( $old_actions, $add_actions, $rem_actions );

        return $new;
    }

    /**
     * Is this an exclusive user group type, where a user can only belong to one
     * group within the taxonomy?
     *
     * @since 2.0.0
     *
     * @return bool
     */
    public function is_exclusive() {
        return  true === $this->args['exclusive'];
    }
}
