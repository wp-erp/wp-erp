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

        //$this->action( 'admin_notices', 'promotional_offer' );
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
        // Show only to Admins
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // check if it has already been dismissed
        $hide_notice = get_option( 'erp_promotional_offer_notice_quiz-aug17', 'no' );

        if ( 'hide' == $hide_notice ) {
            return;
        }
        ?>
            <div class="notice is-dismissible" id="erp-promotional-offer-notice">
                <table>
                    <tbody>
                        <tr>
                            <td class="image-container">
                                <img src="https://ps.w.org/erp/assets/icon-256x256.png" alt="">
                            </td>
                            <td class="message-container">
                                <h2><span class="dashicons dashicons-awards"></span> Big Discount!</h2>
                                <p>
                                    <a href="https://wperp.com/in/WPERP-Quiz" class="highlight-text" target="_blank">Play This Quiz</a> on WP ERP and Win Massive 50% Discount. Hurry Up! Limited Time Offer!
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <span class="dashicons dashicons-megaphone"></span>
            </div><!-- #erp-promotional-offer-notice -->

            <style>
                #erp-promotional-offer-notice {
                    background-color: #089dd7;
                    border: 0px;
                    padding: 0;
                    opacity: 0;
                }

                .wrap > #erp-promotional-offer-notice {
                    opacity: 1;
                }

                #erp-promotional-offer-notice table {
                    border-collapse: collapse;
                    width: 100%;
                }

                #erp-promotional-offer-notice table td {
                    padding: 0;
                }

                #erp-promotional-offer-notice table td.image-container {
                    background-color: #fff;
                    vertical-align: middle;
                    width: 95px;
                }


                #erp-promotional-offer-notice img {
                    max-width: 100%;
                    max-height: 100px;
                    vertical-align: middle;
                }

                #erp-promotional-offer-notice table td.message-container {
                    padding: 0 10px;
                }

                #erp-promotional-offer-notice h2 {
                    color: rgba(250, 250, 250, 0.77);
                    margin-bottom: 10px;
                    font-weight: normal;
                    margin: 16px 0 14px;
                    -webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                }


                #erp-promotional-offer-notice h2 span {
                    position: relative;
                    top: 0;
                }

                #erp-promotional-offer-notice p {
                    color: rgba(250, 250, 250, 0.77);
                    font-size: 14px;
                    margin-bottom: 10px;
                    -webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                }

                #erp-promotional-offer-notice p strong.highlight-text{
                    color: #fff;
                }

                #erp-promotional-offer-notice p a {
                    color: #fafafa;
                }

                #erp-promotional-offer-notice .notice-dismiss:before {
                    color: #fff;
                }

                #erp-promotional-offer-notice span.dashicons-megaphone {
                    position: absolute;
                    bottom: 46px;
                    right: 119px;
                    color: rgba(253, 253, 253, 0.29);
                    font-size: 96px;
                    transform: rotate(-21deg);
                }

            </style>

            <script type='text/javascript'>
                jQuery('body').on('click', '#erp-promotional-offer-notice .notice-dismiss', function(e) {
                    e.preventDefault();

                    wp.ajax.post('erp-dismiss-promotional-offer-notice', {
                        dismissed: true
                    });
                });
            </script>
        <?php
    }
}

new Admin_Page();
