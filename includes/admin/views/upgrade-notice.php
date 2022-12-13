<?php
/**
 * @var bool $is_erp_installed
 * $var string $core_plugin_file
 */

$free_or_pro_title = $core_version_need_to_update ? __( 'CORE', 'erp' ) : __( 'PRO', 'erp' );
$free_or_pro_desc = $core_version_need_to_update ? sprintf( __( 'We’ve pushed a major update on both %1$s and %2$s that requires you to use latest version of both. Please update your %3$s to the latest version', 'erp' ), '<b>WP ERP Free</b>', '<b>WP ERP</b>', '<b>WP ERP Free</b>' )
    : sprintf( __( 'We’ve pushed a major update on both %1$s and %2$s Pro that requires you to use latest version of both. Please update your %3$s to the latest version', 'erp' ), '<b>WP ERP Free</b>', '<b>WP ERP</b>', '<b>ERP Pro</b>' );
$plugin = $core_version_need_to_update ? 'erp' : 'erp-pro';
?>
<div class="erp-core-missing-notice erp-alert notice">
    <div class="notice-content">
        <div class="logo-wrap">
            <div class="erp-logo">
                <img src="<?php echo WPERP_ASSETS . '/images/warning.svg'; ?>" id='slide-3' />
            </div>
        </div>
        <div class="erp-message">
            <h3 class='title'><?php esc_html_e( 'Please update WP ERP ' . $free_or_pro_title, 'erp' ); ?></h3>
            <div><?php echo $free_or_pro_desc; ?></div>
            <?php
            if ( $core_version_need_to_update ) {
                ?>
                <button class="erp-btn erp-btn-primary install-erp-core"><?php esc_html_e( 'Upgrade Now', 'erp' ); ?></button>
                <?php
            } else {
                ?>
                <a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=erp-pro' ), 'upgrade-plugin_erp-pro' ) ); ?>" class="erp-btn erp-btn-primary"><?php esc_html_e( 'Upgrade Now', 'erp' ); ?></a>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<style>
    .erp-core-missing-notice {
        padding: 30px !important;
        gap: 16px;
        background: #FFFFFF;
        box-shadow: 0px 20px 25px -5px rgba(0, 0, 0, 0.1), 0px 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .erp-core-missing-notice.notice {
        border-width: 0;
        padding: 0;
        box-shadow: none;
    }

    .erp-core-missing-notice.erp-alert {
        border-left: 2px solid #b44445;
    }

    .erp-core-missing-notice .notice-content {
        display: flex;
        padding: 16px 20px;
        border-radius: 0 5px 5px 0;
        background: #fff;
    }

    .erp-core-missing-notice .logo-wrap {
        position: relative;
    }

    .erp-core-missing-notice .logo-wrap .erp-logo {
        width: 60px;
        height: 60px;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .erp-core-missing-notice .erp-message {
        margin: 0 23px;
    }

    .erp-core-missing-notice .erp-message h3 {
        margin: 0 0 10px;
        font-family: 'Segoe UI', sans-serif;
        font-style: normal;
        font-weight: 500;
        font-size: 18px;
        line-height: 24px;
        color: #111827;
    }

    .erp-core-missing-notice .erp-message div {
        color: #4b4b4b;
        font-family: 'Segoe UI', sans-serif;
        font-style: normal;
        font-weight: 400;
        font-size: 14px;
        line-height: 20px;
    }

    .erp-core-missing-notice .erp-message .erp-btn {
        font-size: 12px;
        font-weight: 300;
        padding: 8px 15px;
        margin-right: 15px;
        margin-top: 10px;
        border-radius: 3px;
        border: 1px solid #00769d;
        cursor: pointer;
        transition: all .2s linear;
        text-decoration: none;
        font-family: "Segoe UI", sans-serif;
        display: inline-block;
    }

    .erp-core-missing-notice .erp-message .erp-btn-primary {
        color: #fff;
        background: #2579B1;
        margin-right: 15px;
        font-weight: 400;
    }

    .erp-core-missing-notice .erp-message .erp-btn-primary:hover {
        background: transparent;
        color: #2579B1;
    }

    .erp-core-missing-notice .erp-message .erp-btn:disabled {
        opacity: .7;
    }

    .erp-core-missing-notice .erp-message a {
        text-decoration: none;
    }

    .erp-core-missing-notice .close-notice {
        position: absolute;
        top: 10px;
        right: 13px;
        border: 0;
        background: transparent;
        text-decoration: none;
    }

    .erp-core-missing-notice .close-notice span {
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c2c2c2;
        transition: all .2s ease;
        cursor: pointer;
        border: 1px solid #f3f3f3;
        border-radius: 55px;
        width: 20px;
        height: 20px;
    }

    .erp-core-missing-notice .close-notice span:hover {
        color: #f16982;
        border-color: #f16982;
    }
</style>

<script type="text/javascript">
    (function ($) {
        $('.erp-core-missing-notice button.install-erp-core').on('click', function (e) {
            e.preventDefault();

            const self = $(this);

            self.attr('disabled', 'disabled');

            const data = {
                action: 'update_erp_core',
                plugin: "<?php echo $plugin; ?>",
                _wpnonce: '<?php echo wp_create_nonce( 'erp-pro-updater-nonce' ); ?>'
            };

            self.text('<?php echo esc_js( 'Upgrading...', 'erp' ); ?>');

            $.post(ajaxurl, data, function (response) {
                if (response.success) {
                    self.text('<?php echo esc_js( 'Installed', 'erp' ); ?>');
                    window.location.reload();
                }else{
                    alert('Something went wrong');
                    window.location.reload();
                }
            });
        });
    })(jQuery);
</script>
