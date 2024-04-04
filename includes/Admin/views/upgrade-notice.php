<?php
/**
 * @var bool $is_erp_installed
 * $var string $core_plugin_file
 */
?>
<div class="erp-core-missing-notice erp-alert notice">
    <div class="notice-content">
        <div class="logo-wrap">
            <div class="erp-logo">
                <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/warning.svg' ); ?>" id='slide-3' />
            </div>
        </div>
        <div class="erp-message">
            <h3 class='title'><?php echo wp_kses_post( __( 'Please update <b>WP ERP PRO</b>', 'erp' ) ); ?></h3>
            <div><?php echo wp_kses_post( __( 'We’ve pushed a major update on both <b>WP ERP Free</b> and <b>WP ERP Pro</b> that requires you to use latest version of both. Please update your <b>ERP Pro</b> to the latest version', 'erp' )); ?></div>
            <div class='notice-button'>
                <a href="<?php echo esc_url( self_admin_url( 'plugins.php' ) ); ?>" class='erp-btn erp-btn-primary install-erp-core'><?php esc_html_e( 'Upgrade Now', 'erp' ); ?></a>
            </div>
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

    .notice-button{
        margin-top: 30px;
        text-align: end;
    }
    .notice{
        position: fixed;
        width: 150%;
        height: 100%;
        top: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        margin: 0 0 0 -50% !important;
        z-index: 99;
    }
    .erp-core-missing-notice.notice {
        border-width: 0;
        padding: 0;
        box-shadow: none;
    }

    .erp-core-missing-notice .notice-content {
        display: flex;
        padding: 30px 30px 0px;
        background: #fff;

        z-index: 1;
        position: fixed;
        max-width: 570px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        border-radius: 8px;
        height: 200px;
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
        margin: 0 5px;
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
