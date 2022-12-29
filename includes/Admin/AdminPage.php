<?php

namespace WeDevs\ERP\Admin;

use WeDevs\ERP\Framework\Traits\Hooker;

class AdminPage {
    use Hooker;

    public function __construct() {
        $this->init_actions();
        $this->init_classes();
    }

    /**
     * Initialize action hooks
     *
     * @return void
     */
    public function init_actions() {
        $this->action( 'init', 'includes' );
        $this->action( 'admin_init', 'admin_redirects' );
        add_action( 'admin_footer', 'erp_include_popup_markup' );
        $this->action( 'admin_notices', 'promotional_offer' );
    }

    /**
     * Include required files
     *
     * @return void
     */
    public function includes() {
        // Setup/welcome
        if ( ! empty( $_GET['page'] ) ) {
            if ( 'erp-setup' == $_GET['page'] ) {
                new SetupWizard();
            }
        }
    }

    /**
     * Initialize required classes
     *
     * @return void
     */
    public function init_classes() {
        new FormHandler();
        new Ajax();
    }

    /**
     * Handle redirects to setup/welcome page after install and updates.
     *
     * @return void
     */
    public function admin_redirects() {
        if ( ! get_transient( '_erp_activation_redirect' ) ) {
            return;
        }

        delete_transient( '_erp_activation_redirect' );

        if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], [ 'erp-setup', 'erp-welcome' ] ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // If it's the first time
        if ( get_option( 'erp_setup_wizard_ran' ) != '1' ) {
            wp_safe_redirect( admin_url( 'index.php?page=erp-setup' ) );
            exit;

        // Otherwise, the welcome page
        } else {
            wp_safe_redirect( admin_url( 'index.php?page=erp-welcome' ) );
            exit;
        }
    }

    /**
     * Promotional offer notice
     *
     * @since 1.1.15
     *
     * @return void
     */
    public function promotional_offer() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // check if it has already been dismissed
        $offer_key        = 'erp_wedevs_19_blackfriday';
        $offer_start_date = strtotime( '2019-11-20 00:00:01' );
        $offer_end_date   = strtotime( '2019-12-04 23:59:00' );
        $hide_notice      = get_option( $offer_key, 'show' );

        if ( 'hide' == $hide_notice ) {
            return;
        }

        if ( $offer_start_date < current_time( 'timestamp' ) && current_time( 'timestamp' ) < $offer_end_date ) {
            ?>
                <div class="notice notice-success is-dismissible" id="erp-christmas-notice">
                    <div class="logo">
                        <img src="<?php echo esc_url( WPERP_ASSETS . '/images/promo-logo.png' ); ?>" alt="ERP">
                    </div>
                    <div class="content">
                        <p>Biggest Sale of the year on this</p>

                        <h3>Black Friday &amp; <span class="highlight-green">Cyber Monday</span></h3>
                        <p>Claim your discount on <span class="highlight-blue">WP ERP</span> till 4th December</p>
                    </div>
                    <div class="call-to-action">
                        <a target="_blank" href="https://wperp.com/pricing?utm_campaign=black_friday_&_cyber_monday&utm_medium=banner&utm_source=plugin_dashboard">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/promo-btn.png' ); ?>" alt="Btn">
                        </a>
                        <p>
                            <span class="highlight-green">COUPON: </span>
                            <span class="coupon-code">BFCM2019</span>
                        </p>
                    </div>
                </div>

                <style>
                    #erp-christmas-notice {
                        font-size: 14px;
                        border-left: none;
                        background: #000DFF;
                        color: #fff;
                        display: flex
                    }

                    #erp-christmas-notice .notice-dismiss:before {
                        color: #76E5FF;
                    }

                    #erp-christmas-notice .notice-dismiss:hover:before {
                        color: #b71c1c;
                    }

                    #erp-christmas-notice .logo {
                        text-align: center;
                        text-align: center;
                        margin: auto 50px;
                    }

                    #erp-christmas-notice .logo img {
                        width: 80%;
                    }

                    #erp-christmas-notice .highlight-green {
                        color: #4FFF67;
                    }

                    #erp-christmas-notice .highlight-blue {
                        color: #76E5FF;
                    }

                    #erp-christmas-notice .content {
                        margin-top: 5px;
                    }

                    #erp-christmas-notice .content h3 {
                        color: #FFF;
                        margin: 12px 0 5px;
                        font-weight: normal;
                        font-size: 30px;
                    }

                    #erp-christmas-notice .content p {
                        margin-top: 12px;
                        padding: 0;
                        letter-spacing: .4px;
                        color: #ffffff;
                        font-size: 15px;
                    }

                    #erp-christmas-notice .call-to-action {
                        margin-left: 10%;
                        margin-top: 20px;
                    }

                    #erp-christmas-notice .call-to-action a:focus {
                        box-shadow: none;
                    }

                    #erp-christmas-notice .call-to-action p {
                        font-size: 16px;
                        color: #fff;
                        margin-top: 1px;
                        text-align: center;
                    }

                    #erp-christmas-notice .coupon-code {
                        -moz-user-select: all;
                        -webkit-user-select: all;
                        user-select: all;
                    }
                </style>

                <script type='text/javascript'>
                    jQuery('body').on('click', '#erp-christmas-notice .notice-dismiss', function(e) {
                        e.preventDefault();

                        wp.ajax.post( 'erp-dismiss-promotional-offer-notice', {
                            erp_christmas_dismissed: true,
                            nonce: '<?php echo esc_attr( wp_create_nonce( 'erp_admin' ) ); ?>'
                        });
                    });
                </script>
            <?php
        }
    }
}
