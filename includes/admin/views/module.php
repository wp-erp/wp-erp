<style type="text/css">
    .wrap * {
        box-sizing: border-box;
    }

    .wrap .page_title h1 {
        padding: 0;
        margin: 20px 0;
        font-size: 24px;
        color: #000000;
        letter-spacing: 0.22px;
    }

    .modules_wrap {
        margin-bottom: 20px;
    }

    .module_items {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }

    .module_item_col {
        padding: 0 10px;
        max-width: 381px;
        flex-basis: 33.33%;
    }

    .module_item {
        padding: 20px;
        background: #FFFFFF;
        border: 1px solid #E2E2E2;
        border-radius: 3px;
    }

    .module_item .icon {
        width: 44px;
        height: 44px;
        border-radius: 100%;
        margin-bottom: 14px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .module_item.hrm .icon {
        background-image: linear-gradient(180deg, #C2E998 0%, #02CC87 100%);
    }

    .module_item.crm .icon {
        background-image: linear-gradient(180deg, #72D0FF 0%, #349EFA 100%);
    }

    .module_item.accounting .icon {
        background-image: linear-gradient(180deg, #FEDB4D 0%, #FFB84E 100%);
    }

    .module_item h3 {
        font-size: 15px;
        color: #000000;
        letter-spacing: 0.14px;
        line-height: 21px;
        margin: 0 0 5px 0;
    }

    .module_item .subtitle {
        padding: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #788383;
        letter-spacing: 0.11px;
    }

    .module_item .switch {
        min-width: 50px;
        max-width: 50px;
    }

    .erp_addon {
        background: #FFFFFF;
        border: 1px solid #E2E2E2;
        border-radius: 3px;
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 22px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 2px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .erp-help-tip {
        left: 0.1rem;
        bottom: 0.3rem;
    }
</style>

<div class="wrap erp-settings">

    <div class="page_title">
        <h1>
            <?php esc_html_e( 'Modules', 'erp' ); ?>
            <?php echo erp_help_tip( esc_html__( "If you do not require any of the modules, you may deactivate it from here.", 'erp' ) ); ?>
        </h1>
    </div>

    <div class="modules_wrap">
        <div class="module_items">
            <div class="module_item_col">
                <div class="module_item hrm">
                    <div class="icon">
                        <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/hrm.svg' ); ?>" alt="<?php echo esc_attr( 'HRM' ); ?>" />
                    </div>
                    <h3 class="title"><?php esc_html_e( 'HR Management', 'erp' );?></h3>
                    <div class="subtitle">
                        <span><?php esc_html_e( 'Human Resource Management', 'erp' ); ?></span>
                        <label class="switch">
                            <input class="module_action" type="checkbox" data-module-id="hrm" <?php echo wperp()->modules->is_module_active('hrm') ? 'checked="checked"' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="module_item_col">
                <div class="module_item crm">
                    <div class="icon">
                        <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/crm.svg' ); ?>" alt="<?php echo esc_attr( 'CRM' ); ?>" />
                    </div>
                    <h3 class="title"><?php esc_html_e( 'CR Management', 'erp' );?></h3>
                    <div class="subtitle">
                        <span><?php esc_html_e( 'Cusomer Relationship Management', 'erp' ); ?></span>
                        <label class="switch">
                            <input class="module_action" type="checkbox" data-module-id="crm" <?php echo wperp()->modules->is_module_active('crm') ? 'checked="checked"' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="module_item_col">
                <div class="module_item accounting">
                    <div class="icon">
                        <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/accounting.svg' ); ?>" alt="<?php echo esc_attr( 'Accounting' ); ?>" />
                    </div>
                    <h3 class="title"><?php esc_html_e( 'Accounting', 'erp' );?></h3>
                    <div class="subtitle">
                        <span><?php esc_html_e( 'Accounting Management', 'erp' ); ?></span>
                        <label class="switch">
                            <input class="module_action" type="checkbox" data-module-id="accounting" <?php echo wperp()->modules->is_module_active('accounting') ? 'checked="checked"' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<script type="text/javascript">
    jQuery( function( $ ) {

        $( '.module_action' ).click( function() {
            var module_id  = $(this).data('module-id');
            var state      = $(this).prop( 'checked' );
            var toggle     = ( state ) ? 'activate' : 'deactivate';

            toastr.success( '<?php echo __( 'Please wait!', 'erp'); ?>', '', {timeOut: 1000} );

            wp.ajax.send( 'erp-toggle-module', {
                data: {
                    '_wpnonce': '<?php echo wp_create_nonce( 'wp-erp-toggle-module' )  ?>',
                    module_id:  module_id,
                    toggle:     toggle
                },
                success: function( resp ) {
                    toastr.success( resp );
                    setTimeout( function() {
                        location.reload();
                    }, 1000 )
                },
                error: function( response ) {
                    toastr.error( response );
                }
            });

        } );

    });
</script>
