<?php

/**
 * Design class
 *
 * @author Tareq Hasan <tareq@weDevs.com>
 */
class bedIQ_Settings_Design extends bedIQ_Settings_Page {

    function __construct() {
        $this->id = 'design';
        $this->label = 'Design';
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $settings = array(
            array( 'title' => __( '', 'bediq' ), 'type' => 'title', 'desc' => '', 'id' => 'design_options' ),

            array(
                'title'     => __( 'Logo', 'bediq' ),
                'desc'      => __( 'It will replace the textual logo (site name) on the header.', 'bediq' ),
                'id'        => 'logo',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  true,
            ),
            array(
                'title'     => __( 'Favicon', 'bediq' ),
                'desc'      => __( 'The favicon is a small, 16x16 icon that appears besides your URL in the address bar, in open tabs and/or in bookmarks.', 'bediq' ),
                'id'        => 'favicon',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  true,
            ),

            array( 'type' => 'sectionend', 'id' => 'design_options'),

            array( 'title' => __( 'Touch Icons', 'bediq' ), 'type' => 'title', 'desc' => 'Touch Icons are the icons used in mobiles devices such as iOS and Android phones and tablets. You can upload images optimized for each category of devices. The images should be in PNG format.', 'id' => 'design_options' ),

            array(
                'title'     => __( 'Icon: 72x72 px', 'bediq' ),
                'desc'      => __( 'For non-Retina iPhone, iPod Touch, and Android 2.1+ devices.', 'bediq' ),
                'id'        => '72',
                'default'   => '0',
                'type'      => 'image',
            ),
            array(
                'title'     => __( 'Icon: 76x76 px', 'bediq' ),
                'desc'      => __( 'For the iPad mini and the first- and second-generation iPad on iOS ≤ 6.', 'bediq' ),
                'id'        => '76',
                'default'   => '',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 114x114 px', 'bediq' ),
                'desc'      => __( 'For iPhone with high-resolution Retina display running iOS ≤ 6', 'bediq' ),
                'id'        => '114',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 120x120 px', 'bediq' ),
                'desc'      => __( 'For iPhone with high-resolution Retina display running iOS ≥ 7', 'bediq' ),
                'id'        => '120',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 144x144 px', 'bediq' ),
                'desc'      => __( 'For iPad with high-resolution Retina display running iOS ≤ 6', 'bediq' ),
                'id'        => '144',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),
            array(
                'title'     => __( 'Icon: 152x152 px', 'bediq' ),
                'desc'      => __( 'For iPad with high-resolution Retina display running iOS ≥ 7', 'bediq' ),
                'id'        => '152',
                'default'   => '0',
                'type'      => 'image',
                'tooltip'   =>  false
            ),

            array( 'type' => 'sectionend', 'id' => 'design_options'),
        );

        return apply_filters( 'bediq_settings_design_fields', $settings );
    }
}

return new bedIQ_Settings_Design();