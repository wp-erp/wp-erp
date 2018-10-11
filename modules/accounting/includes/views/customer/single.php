<div class="wrap erp-accounting">
<?php
$page = isset( $_GET['page'] ) && $_GET['page'] == 'erp-accounting' ? $_GET['page'] : '';
$section = isset( $_GET['section'] ) && $_GET['section'] == 'customers' ? $_GET['section'] : '';
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$edit_url = admin_url( 'admin.php?page=' . $page . '&section=' . $section . '&action=edit&id=' . $customer_id );
?>
    <h2><?php echo $customer->get_full_name(); ?> <a href="<?php echo $edit_url; ?>" class="add-new-h2"><?php _e( 'Edit', 'erp' ); ?></a></h2>

    <?php
    $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'transactions';

    $trans = WeDevs\ERP\Accounting\Model\Transaction::OfUser( $customer->id )->with( 'items' )->get();
    ?>

    <?php include_once dirname( dirname( __FILE__ ) ) . '/common/transaction-chart.php'; ?>

    <h2 class="nav-tab-wrapper erp-nav-tab-wrapper" style="margin: 20px 0;">
        <a class="nav-tab<?php echo $current_tab == 'transactions' ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=erp-accounting&section=customers&action=view&id=' . $id . '&tab=transactions' ); ?>"><?php _e( 'Transactions', 'erp' ); ?></a>
        <a class="nav-tab<?php echo $current_tab == 'details' ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=erp-accounting&section=customers&action=view&id=' . $id . '&tab=details' ); ?>"><?php _e( 'User Details', 'erp' ); ?></a>
    </h2>

    <?php
    if ( 'transactions' == $current_tab ) {

        include dirname( __FILE__ ) . '/user-transactions.php';

    } elseif ( 'details' == $current_tab ) {

        include dirname( __FILE__ ) . '/user-details.php';

    }
    ?>
</div>

