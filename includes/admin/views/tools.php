<div class="wrap">

    <?php
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        $is_crm_activated = erp_is_module_active( 'crm' );
        $is_hrm_activated = erp_is_module_active( 'hrm' );

        $erp_import_export_fields = erp_get_import_export_fields();
        $keys = array_keys( $erp_import_export_fields );

        $import_export_types = [];
        foreach ( $keys as $type ) {
            $import_export_types[ $type ] = __( ucwords( $type ), 'erp' );
        }

        if ( ! $is_crm_activated ) {
            unset( $import_export_types['contact'] );
            unset( $import_export_types['company'] );
        }

        if ( ! $is_hrm_activated ) {
            unset( $import_export_types['employee'] );
        }

        $tabs = [
            'general' => __( 'General', 'erp' ),
        ];

        if ( $is_crm_activated || $is_hrm_activated ) {
            $tabs['import'] = __( 'Import', 'erp' );
            $tabs['export'] = __( 'Export', 'erp' );
        }

        $tabs['misc'] = __( 'Misc.', 'erp' );
        $tabs = apply_filters( 'erp_tools_tabs', $tabs );
    ?>

    <h2 class="nav-tab-wrapper erp-nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_label) { ?>
            <a class="nav-tab <?php echo ( $current_tab == $tab_key ) ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=erp-tools&tab=' ) . $tab_key; ?>"><?php echo $tab_label ?></a>
        <?php } ?>
    </h2>

    <div class="metabox-holder">

        <?php
        switch ( $current_tab ) {
            case 'import':
                include __DIR__ . '/tools/import.php';
                break;

            case 'export':
                include __DIR__ . '/tools/export.php';
                break;

            case 'misc':
                include __DIR__ . '/tools/misc.php';
                break;

            case 'general':
                include __DIR__ . '/tools/general.php';
                break;

            default:
                do_action( 'erp_tools_page_' . $current_tab );
                break;
        }
        ?>

    </div><!-- .metabox-holder -->
</div>
