<?php

/**
 * General class
 */
class bedIQ_Settings_Content extends bedIQ_Settings_Page {


    function __construct() {
        $this->id = 'content';
        $this->label = __( 'Content', 'bediq' );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'Content Settings', 'bediq' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Pagination', 'bediq' ),
                'desc'      => __( 'Select how you want pagination links to be displayed. You can display the traditional "Previous" and "Next" links, or the recommended, numbered links.', 'bediq' ),
                'id'        => 'pagination',
                'default'   => 'paginate_links',
                'type'      => 'radio',
                'tooltip'   =>  true,
                'options' => array(
                    'paginate_links' => __( 'Numbered pagination', 'bediq' ),
                    'prevnext'       => __( '"Previous - Next" links.', 'bediq' ),
                )
            ),

            array(
                'title' => __( 'Content Preview', 'bediq' ),
                'desc'      => __( 'You can select whether you want the Content or the Excerpt to be displayed on listing pages.', 'bediq' ),
                'id'        => 'use_content',
                'default'   => 'excerpt',
                'type'      => 'radio',
                'tooltip'   =>  true,
                'options' => array(
                    'content' => __( 'Full Content', 'bediq' ),
                    'excerpt' => __( 'Excerpt', 'bediq' ),
                )
            ),

            array(
                'title' => __( 'Read More text', 'bediq' ),
                'desc'      => __( 'You can set what the Read More text will be. This applies to both the Content and the Excerpt.', 'bediq' ),
                'id'        => 'read_more',
                'default'   => 'Read More &rarr;',
                'type'      => 'text',
                'tooltip'   =>  true
            ),

            array(
                'title' => __( 'Excerpt length', 'bediq' ),
                'desc'      => __( 'You can define how long the Excerpt will be (in words). You can also set the text that appears when the excerpt is auto-generated and is automatically cut-off. These options only apply to the Excerpt.', 'bediq' ),
                'id'        => 'excerpt_length',
                'default'   => '50',
                'type'      => 'text',
                'tooltip'   =>  true
            ),

            array(
                'title' => __( 'Comments on Pages', 'bediq' ),
                'desc'      => __( 'Disable comments on pages', 'bediq' ),
                'id'        => 'page_comment',
                'default'   => 'yes',
                'type'      => 'checkbox',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'bediq_settings_content', $fields );
    }
}

return new bedIQ_Settings_Content();