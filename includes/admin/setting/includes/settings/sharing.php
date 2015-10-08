<?php

/**
 * General class
 */
class bedIQ_Settings_Sharing extends bedIQ_Settings_Page {


    function __construct() {
        $this->id = 'sharing';
        $this->label = __( 'Sharing', 'bediq' );
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

        return apply_filters( 'bediq_sharing_services', $services );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $post_types = $this->get_post_types();

        $fields = array(

            array( 'title' => __( 'Sharing Settings', 'bediq' ), 'type' => 'title', 'desc' => __( 'Add sharing buttons to your blog and allow your visitors to share posts with their friends.', 'bediq' ) ),

            array(
                'title'   => __( 'Open links in', 'bediq' ),
                'id'      => 'open_links',
                'default' => 'blank',
                'type'    => 'select',
                'options' => array(
                    'blank' => __( 'New Window', 'bediq' ),
                    'self'  => __( 'Same Window', 'bediq' )
                )
            ),
            array(
                'title'   => __( 'Show Services', 'bediq' ),
                'id'      => 'services',
                'type'    => 'multicheck',
                'options' => $this->get_services()
            ),
            array(
                'title'   => __( 'Show share buttons on', 'bediq' ),
                'id'      => 'sharing_on',
                'default' => array( 'post' ),
                'type'    => 'multicheck',
                'options' => $post_types
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'bediq_settings_seo', $fields );
    }
}

return new bedIQ_Settings_Sharing();