<?php

/**
 * General class
 */
class ERP_Settings_Content extends ERP_Settings_Page {


    function __construct() {
        $this->id = 'content';
        $this->label = __( 'Content', 'erp' );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'Content Settings', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Pagination', 'erp' ),
                'desc'      => __( 'Select how you want pagination links to be displayed. You can display the traditional "Previous" and "Next" links, or the recommended, numbered links.', 'erp' ),
                'id'        => 'pagination',
                'default'   => 'paginate_links',
                'type'      => 'radio',
                'tooltip'   =>  true,
                'options' => array(
                    'paginate_links' => __( 'Numbered pagination', 'erp' ),
                    'prevnext'       => __( '"Previous - Next" links.', 'erp' ),
                )
            ),

            array(
                'title' => __( 'Content Preview', 'erp' ),
                'desc'      => __( 'You can select whether you want the Content or the Excerpt to be displayed on listing pages.', 'erp' ),
                'id'        => 'use_content',
                'default'   => 'excerpt',
                'type'      => 'radio',
                'tooltip'   =>  true,
                'options' => array(
                    'content' => __( 'Full Content', 'erp' ),
                    'excerpt' => __( 'Excerpt', 'erp' ),
                )
            ),

            array(
                'title' => __( 'Read More text', 'erp' ),
                'desc'      => __( 'You can set what the Read More text will be. This applies to both the Content and the Excerpt.', 'erp' ),
                'id'        => 'read_more',
                'default'   => 'Read More &rarr;',
                'type'      => 'text',
                'tooltip'   =>  true
            ),

            array(
                'title' => __( 'Excerpt length', 'erp' ),
                'desc'      => __( 'You can define how long the Excerpt will be (in words). You can also set the text that appears when the excerpt is auto-generated and is automatically cut-off. These options only apply to the Excerpt.', 'erp' ),
                'id'        => 'excerpt_length',
                'default'   => '50',
                'type'      => 'text',
                'tooltip'   =>  true
            ),

            array(
                'title' => __( 'Comments on Pages', 'erp' ),
                'desc'      => __( 'Disable comments on pages', 'erp' ),
                'id'        => 'page_comment',
                'default'   => 'yes',
                'type'      => 'checkbox',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_content', $fields );
    }
}

return new ERP_Settings_Content();