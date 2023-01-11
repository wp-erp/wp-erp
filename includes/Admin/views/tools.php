<div class="wrap">

    <?php
    $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

    $is_crm_activated = erp_is_module_active( 'CRM' );
    $is_hrm_activated = erp_is_module_active( 'HRM' );
    $is_acc_activated = erp_is_module_active( 'Accounting' );

    $erp_import_export_fields = erp_get_import_export_fields();
    $keys                     = array_keys( $erp_import_export_fields );

    $import_export_types = [];

    foreach ( $keys as $type ) {
        $import_export_types[ $type ] = sprintf( esc_html__( '%s', 'erp' ), ucwords( $type ) );
    }

    if ( ! $is_crm_activated ) {
        unset( $import_export_types['contact'] );
        unset( $import_export_types['company'] );
    }

    if ( ! $is_hrm_activated ) {
        unset( $import_export_types['employee'] );
    }

    if ( ! $is_acc_activated ) {
        unset( $import_export_types['vendor'] );
    }

    $tabs = [
        'general' => esc_html__( 'General', 'erp' ),
    ];

    $tabs['misc']        = esc_html__( 'Misc.', 'erp' );
    $tabs['status']      = esc_html__( 'Status', 'erp' );
    $tabs['log']         = esc_html__( 'Audit Log', 'erp' );
    $tabs['danger-zone'] = esc_html__( 'Danger Zone', 'erp' );


    $tabs = apply_filters( 'erp_tools_tabs', $tabs );
    ?>

    <h2 class="nav-tab-wrapper erp-nav-tab-wrapper erp-tools-navbar">
        <?php foreach ( $tabs as $tab_key => $tab_label ) { ?>
            <a class="nav-tab <?php echo esc_attr( ( $current_tab === $tab_key ) ? 'nav-tab-active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=erp-tools&tab=' ) ) . esc_html( $tab_key ); ?>"><?php echo esc_html( $tab_label ); ?></a>
        <?php } ?>
    </h2>

    <div class="metabox-holder">

        <?php
        switch ( $current_tab ) {
            case 'general':
                include __DIR__ . '/tools/general.php';
                break;

            case 'misc':
                include __DIR__ . '/tools/misc.php';
                break;

            case 'status':
                new \WeDevs\ERP\Status();
                break;

            case 'log':
                include_once __DIR__ . '/log.php';
                break;

            case 'danger-zone':
                new \WeDevs\ERP\DangerZone();
                break;

            default:
                do_action( 'erp_tools_page_' . $current_tab );
                break;
        }
        ?>

    </div><!-- .metabox-holder -->
</div>
