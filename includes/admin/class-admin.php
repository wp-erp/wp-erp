<?php
namespace WeDevs\ERP\Admin;
use WeDevs\ERP\Framework\Traits\Hooker;

class Admin_Page {

    use Hooker;

    function __construct() {
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
        // $this->action( 'admin_notices', 'accounting_survey_notice' );
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
                include_once dirname( __FILE__ ) . '/class-setup-wizard.php';
            }
        }
    }

    /**
     * Initialize required classes
     *
     * @return void
     */
    public function init_classes() {
        new Form_Handler();
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

        if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'erp-setup', 'erp-welcome' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) {
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

        if ( ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] !== 'erp' ) {
            return;
        }

        // check if it has already been dismissed
        $offer_key        = 'erp_wedevs_19_blackfriday';
        $offer_start_date = strtotime( '2019-11-26 00:00:01' );
        $offer_end_date   = strtotime( '2019-12-04 23:59:00' );
        $hide_notice      = get_option( $offer_key, 'show' );

        if ( 'hide' == $hide_notice ) {
            return;
        }

        if ( $offer_start_date < current_time( 'timestamp' ) && current_time( 'timestamp' ) < $offer_end_date ) {
            ?>
                <div class="notice notice-success is-dismissible" id="erp-christmas-notice">
                    <div class="logo">
                        <img src="<?php echo WPERP_ASSETS . '/images/promo-logo.png' ?>" alt="ERP">
                    </div>
                    <div class="content">
                        <h3><span class="highlight-red">Black Friday</span> &amp; <span class="highlight-blue">Cyber Monday</span></h3>
                        <p>Don't miss out on the biggest sale of the year on <span class="highlight-red">WP ERP</span></p>
                        <div class="coupon-box">
                            <div class="highlight-red">Use this coupon</div>
                            <div class="highlight-code">BFCM2019</div>
                        </div>
                    </div>
                    <div class="call-to-action">
                        <a href="https://wperp.com/pricing?utm_campaign=black_friday_&_cyber_monday&utm_medium=banner&utm_source=plugin_dashboard">Save 33%</a>
                        <p>Valid till 4th December.</p>
                    </div>
                </div>

                <style>
                    #erp-christmas-notice {
                        font-size: 14px;
                        border-left: none;
                        background: #000;
                        color: #fff;
                        display: flex
                    }

                    #erp-christmas-notice .logo {
                        text-align: center;
                        text-align: center;
                        margin: 13px 30px 5px 15px;
                    }

                    #erp-christmas-notice .logo img {
                        width: 80%;
                    }

                    #erp-christmas-notice .highlight-red {
                        color: #FF0000;
                    }
                    #erp-christmas-notice .highlight-blue {
                        color: #48ABFF;
                    }

                    #erp-christmas-notice .content {
                        margin-top: 5px;
                    }

                    #erp-christmas-notice .content h3 {
                        color: #FFF;
                        margin: 12px 0px 5px;
                        font-weight: normal;
                        font-size: 20px;
                    }

                    #erp-christmas-notice .content p {
                        margin: 0px 0px;
                        padding: 0px;
                        letter-spacing: 0.4px;
                    }

                    #erp-christmas-notice .coupon-box {
                        margin-top: 10px;
                        display: flex;
                        align-items: center;
                        font-size: 17px;
                    }

                    #erp-christmas-notice .coupon-box .highlight-code {
                        margin-left: 15px;
                        border: 1px dashed;
                        padding: 4px 10px;
                        border-radius: 15px;
                        letter-spacing: 1px;
                        background: #1E1B1B;

                        -webkit-user-select: all;
                        -moz-user-select: all;
                        -ms-user-select: all;
                        user-select: all;
                    }

                    #erp-christmas-notice .call-to-action {
                        margin-left: 8%;
                        margin-top: 25px;
                    }
                    #erp-christmas-notice .call-to-action a {
                        border: none;
                        background: #FF0000;
                        padding: 8px 15px;
                        font-size: 15px;
                        color: #fff;
                        border-radius: 20px;
                        text-decoration: none;
                        display: block;
                        text-align: center;
                    }
                    #erp-christmas-notice .call-to-action p {
                        font-size: 12px;
                        margin-top: 1px;
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

    /**
     * Accounting survey notice
     *
     * @since 1.4.6
     *
     * @return void
     */
    public function accounting_survey_notice() {
        // Show only to Admins
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // check if it has already been dismissed
        $hide_notice = get_option( 'erp_accounting_survey_notice', 'no' );

        if ( 'hide' == $hide_notice ) {
            return;
        } ?>

        <div class="notice notice-info is-dismissible" id="erp-accounting-survey-notice">
            <p>We have completely revamped the Accounting module. Why not check the <a href="<?php echo admin_url('/admin.php?page=erp-accounting#/erp-ac-help') ?>" target="_blank">help</a> page for detailed instructions.</p>
        </div><!-- #erp-accounting-survey-notice -->

        <style>
            #erp-accounting-survey-notice p {
                font-size: 15px;
            }
        </style>

        <script type='text/javascript'>
            jQuery('body').on('click', '#erp-accounting-survey-notice .notice-dismiss', function(e) {
                e.preventDefault();

                wp.ajax.post('erp-dismiss-accounting-survey-notice', {
                    dismissed: true
                });
            });
        </script>
    <?php
    }
}

new Admin_Page();
