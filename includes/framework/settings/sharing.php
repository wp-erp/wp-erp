<?php

/**
 * General class
 */
class ERP_Settings_Sharing extends ERP_Settings_Page {


    function __construct() {
        $this->id = 'sharing';
        $this->label = __( 'Sharing', 'erp' );
    }

    function get_post_types() {
        $post_types = get_post_types(array(), false );
        $types      = array();
        $ignore     = array('attachment', 'revision', 'nav_menu_item' );

        foreach ($post_types as $type) {
            if ( in_array($type->name, $ignore ) ) {
                continue;
            }

            $types[$type->name] = $type->labels->name;
        }

        return $types;
    }

    function get_services() {
        $services = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'gplus' => 'Google Plus',
            'pinterest' => 'Pinterest'
        );

        return apply_filters( 'erp_sharing_services', $services );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $post_types = $this->get_post_types();

        $fields = array(

            array( 'title' => __( 'Sharing Settings', 'erp' ), 'type' => 'title', 'desc' => __( 'Add sharing buttons to your blog and allow your visitors to share posts with their friends.', 'erp' ) ),

            array(
                'title'   => __( 'Open links in', 'erp' ),
                'id'      => 'open_links',
                'default' => 'blank',
                'type'    => 'select',
                'options' => array(
                    'blank' => __( 'New Window', 'erp' ),
                    'self'  => __( 'Same Window', 'erp' )
                )
            ),
            array(
                'title'   => __( 'Show Services', 'erp' ),
                'id'      => 'services',
                'type'    => 'multicheck',
                'options' => $this->get_services()
            ),
            array(
                'title'   => __( 'Show share buttons on', 'erp' ),
                'id'      => 'sharing_on',
                'default' => array( 'post' ),
                'type'    => 'multicheck',
                'options' => $post_types
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_seo', $fields );
    }
}

return new ERP_Settings_Sharing();