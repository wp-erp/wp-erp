<div class="wrap erp-accounting">
<?php 
$page = isset( $_GET['page'] ) && $_GET['page'] == 'erp-accounting-vendors' ? $_GET['page'] : '';
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$edit_url = admin_url( 'admin.php?page=' . $page . '&action=edit&id=' . $customer_id ); 

?>
    <h2><?php echo $vendor->get_full_name(); ?> <a href="<?php echo $edit_url; ?>" class="add-new-h2"><?php _e( 'Edit', 'erp-accounting' ); ?></a></h2>

    <?php
   
    $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'transactions';

    $trans = WeDevs\ERP\Accounting\Model\Transaction::OfUser( $vendor->id )->with( 'items' )->get();

    ?>

    <?php include_once dirname( dirname( __FILE__ ) ) . '/common/transaction-chart.php'; ?>

    <h2 class="nav-tab-wrapper erp-nav-tab-wrapper" style="margin: 20px 0;">
        <a class="nav-tab<?php echo $current_tab == 'transactions' ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page='.$page.'&action=view&id=' . $id . '&tab=transactions' ); ?>"><?php _e( 'Transactions', 'erp-accounting' ); ?></a>
        <a class="nav-tab<?php echo $current_tab == 'details' ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page='.$page.'&action=view&id=' . $id . '&tab=details' ); ?>"><?php _e( 'User Details', 'erp-accounting' ); ?></a>
    </h2>

    <?php
    if ( 'transactions' == $current_tab ) {

        include dirname( __FILE__ ) . '/vendor-transactions.php';

    } elseif ( 'details' == $current_tab ) {

        include dirname( __FILE__ ) . '/vendor-details.php';

    }
    ?>
</div>