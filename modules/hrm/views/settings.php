<?php
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'custom';
$tabs = apply_filters( 'erp_hr_employee_single_tabs', array(
    'custom' => array(
        'title'    => __( 'Custom Fields', 'wp-erp' ),
        'callback' => ''
    ),
    'termination' => array(
        'title'    => __( 'Termination Reasons', 'wp-erp' ),
        'callback' => ''
    ),
) );
?>

<h2 class="nav-tab-wrapper" style="margin-bottom: 15px;">
    <?php foreach ($tabs as $key => $tab) {
        $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
        ?>
        <a href="<?php echo add_query_arg( array( 'tab' => $key ), erp_hr_url_single_employee(1) ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php echo $tab['title'] ?></a>
    <?php } ?>
</h2>

<?php
// call the tab callback function
if ( array_key_exists( $active_tab, $tabs ) && is_callable( $tabs[$active_tab]['callback'] ) ) {
    call_user_func( $tabs[$active_tab]['callback'] );
}
?>