<?php

/**
 * Design class
 *
 * @author Tareq Hasan <tareq@weDevs.com>
 */
class ERP_Settings_Design extends ERP_Settings_Page {

    function __construct() {
        $this->id = 'design';
        $this->label = 'Design';
        $this->sections = $this->get_sections();
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $settings = array(
            array( 'title' => __( '', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'design_options' ),

            array(
                'title'     => __( 'Logo', 'erp' ),
                'desc'      => __( 'It will replace the textual logo (site name) on the header.', 'erp' ),
                'id'        => 'logo',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  true,
            ),
            array(
                'title'     => __( 'Favicon', 'erp' ),
                'desc'      => __( 'The favicon is a small, 16x16 icon that appears besides your URL in the address bar, in open tabs and/or in bookmarks.', 'erp' ),
                'id'        => 'favicon',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  true,
            ),

            array( 'type' => 'sectionend', 'id' => 'design_options'),

            array( 'title' => __( 'Touch Icons', 'erp' ), 'type' => 'title', 'desc' => 'Touch Icons are the icons used in mobiles devices such as iOS and Android phones and tablets. You can upload images optimized for each category of devices. The images should be in PNG format.', 'id' => 'design_options' ),

            array(
                'title'     => __( 'Icon: 72x72 px', 'erp' ),
                'desc'      => __( 'For non-Retina iPhone, iPod Touch, and Android 2.1+ devices.', 'erp' ),
                'id'        => '72',
                'default'   => '0',
                'type'      => 'image',
            ),
            array(
                'title'     => __( 'Icon: 76x76 px', 'erp' ),
                'desc'      => __( 'For the iPad mini and the first- and second-generation iPad on iOS ≤ 6.', 'erp' ),
                'id'        => '76',
                'default'   => '',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 114x114 px', 'erp' ),
                'desc'      => __( 'For iPhone with high-resolution Retina display running iOS ≤ 6', 'erp' ),
                'id'        => '114',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 120x120 px', 'erp' ),
                'desc'      => __( 'For iPhone with high-resolution Retina display running iOS ≥ 7', 'erp' ),
                'id'        => '120',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 144x144 px', 'erp' ),
                'desc'      => __( 'For iPad with high-resolution Retina display running iOS ≤ 6', 'erp' ),
                'id'        => '144',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 152x152 px', 'erp' ),
                'desc'      => __( 'For iPad with high-resolution Retina display running iOS ≥ 7', 'erp' ),
                'id'        => '152',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),

            array( 'type' => 'sectionend', 'id' => 'design_options'),
        );

        return apply_filters( 'erp_settings_design_fields', $settings );
    }


    public function get_section_fields( $section = '' ) {

        $fields[''] = array(

            array( 'title' => __( 'General Options', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Site Name', 'erp' ),
                'id'    => 'blogname',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'erp' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'Site Description', 'erp' ),
                'id'    => 'blogdescription',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'erp' ),
                'type'  => 'text',
            ),

            array(
                'title' => __( 'Site Descriptionffffff', 'erp' ),
                'id'    => 'blogdescripfftion',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'erp' ),
                'type'  => 'text',
            ),


            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        $fields['mishu'] = array(

            array( 'title' => __( 'Mishu', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Site Name', 'erp' ),
                'id'    => 'blogname',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'erp' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'Site Description', 'erp' ),
                'id'    => 'blogdescription',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'erp' ),
                'type'  => 'text',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings
       
        return $section === false ? $fields[''] : $fields[$section];
    } 

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            '' => __( 'Checkout Options', 'erp' ),
            'mishu' => __( 'Mishu', 'erp' )
        );

        
        return apply_filters( 'erp_get_sections_' . $this->id, $sections );
    }
}

return new ERP_Settings_Design();