<?php
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'workdays';
$settings   = \WeDevs\ERP\HRM\Settings::init();
$tabs       = $settings->get_tabs();
?>

<h2 class="nav-tab-wrapper" style="margin-bottom: 15px;">
    <?php foreach ($tabs as $key => $tab) {
        $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
        ?>
        <a href="<?php echo add_query_arg( array( 'tab' => $key ), admin_url( 'admin.php?page=erp-hr-settings' ) ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php echo esc_html( $tab['title'] ); ?></a>
    <?php } ?>
</h2>

<?php
// call the tab callback function
if ( array_key_exists( $active_tab, $tabs ) && is_callable( $tabs[$active_tab]['callback'] ) ) {
    call_user_func( $tabs[$active_tab]['callback'] );
}
?>