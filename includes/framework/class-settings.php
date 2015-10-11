<?php

/**
 * ERP Admin settings class
 */
class ERP_Admin_Settings {

    private static $settings = array();
    private static $section = array();

    function __construct() {

    }

    public static function get_settings() {

        if ( !self::$settings ) {

            $settings = array();

            $settings[] = include __DIR__ . '/settings/general.php';
            $settings[] = include __DIR__ . '/settings/design.php';
            $settings[] = include __DIR__ . '/settings/sharing.php';
            $settings[] = include __DIR__ . '/settings/content.php';
            $settings[] = include __DIR__ . '/settings/api-keys.php';
            $settings[] = include __DIR__ . '/settings/others.php';

            self::$settings = apply_filters( 'erp_settings_pages', $settings );
        }

        return self::$settings;
    }


    public static function output() {
        global $current_section, $current_tab, $current_class;

        $settings = self::get_settings();
      

        if ( $settings ) {

            $current_tab     = isset( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : 'general';
            $current_section = isset( $_GET['section'] ) ? sanitize_title( $_GET['section'] ) : false;
           
            echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';

            foreach ( $settings as $obj ) {
          
                $url   = sprintf('admin.php?page=erp-settings&tab=%s', $obj->get_id() );
                $class = ( $current_tab == $obj->get_id() ) ? ' nav-tab-active' : '';

                if ( $current_tab == $obj->get_id() && $current_class === null ) {
                    $current_class = $obj;
                }

                printf('<a class="nav-tab%s" href="%s">%s</a>', $class, $url, $obj->get_label() );
            }

            echo '</h2>';

            echo '<ul class="erp-subsubsub">';
            foreach ($settings as $obj) {

                if ( $current_tab != $obj->get_id() ) {
                    continue;
                }
                $sections = isset( $obj->sections ) ? $obj->sections : array();
                echo '<li>';
                foreach ( $sections as $slug => $label ) {
                    $url    = 'admin.php?page=erp-settings&tab='.$obj->get_id().'&section='.$slug;
                    $class  = ( $current_section == $slug ) ? ' erp-nav-tab-active' : '';
                    $link[] = '<a class="erp-nav-tab'.$class.'" href="'.$url.'">'.$label.'</a>';
                }
                
                echo implode( ' | </li><li>', $link );
                echo '</li>';
            }
            echo '</ul>';

            if ( is_a( $current_class, 'ERP_Settings_Page' ) ) {

                $current_class->save();
                $current_class->output( $current_section );
            }
        }
    }
}