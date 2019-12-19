<?php

if ( ! class_exists( 'WeForms_Upsell' ) ) :

/**
 * weForms Upsell Class
 */
class WeForms_Upsell {

    /**
     * Affiliate code for referal tracking
     *
     * @var string
     */
    private $affiliate;

    /**
     * Learn more link
     *
     * @var string
     */
    private $learn_more = 'https://wedevs.com/weforms-details/';

    /**
     * Instantiate the class
     *
     * @param string $affiliate
     */
    function __construct( $affiliate = '' ) {
        $this->affiliate = $affiliate;

        add_action( 'init', array( $this, 'init_hooks' ) );
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {

        if ( class_exists( 'weForms' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        add_action( 'admin_notices', array( $this, 'activation_notice' ) );

        add_action( 'wp_ajax_weforms_upsell_installer', array( $this, 'install_weforms' ) );
        add_action( 'wp_ajax_weforms_upsell_dismiss', array( $this, 'dismiss_weforms_notice' ) );
    }

    /**
     * Show the plugin installation notice
     *
     * @return void
     */
    public function activation_notice() {

        if ( $this->is_dismissed() ) {
            return;
        }
        ?>
        <div class="updated" id="weforms-upsell-prompt">
            <div class="weforms-upsell-logo">
                <img src="https://wedevs-com-wedevs.netdna-ssl.com/wp-content/uploads/2017/08/weforms-upsell.png" width="272" height="71" alt="weForms Logo">
            </div>
            <div class="weforms-upsell-text">
                <h2>weForms is here!</h2>

                <p>weForms is the next generation contact form plugin for WordPress.</p>
            </div>
            <div class="weforms-upsell-cta">
                <button id="weforms-upsell-prompt-btn" class="button"><?php esc_html_e( 'Install Now', 'weforms' ); ?></button>
                &nbsp;<a href="#" class="learn-more" data-tube="NJvjy9WFyAM">Learn More</a>
            </div>
            <button type="button" class="notice-dismiss" style="padding: 3px;" title="<?php esc_html_e( 'Dismiss this notice.' ); ?>">
                <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.' ); ?></span>
            </button>
        </div>

        <div class="weforms-upsell-modal" id="weforms-upsell-modal">
            <a class="close">
                &times;
                <span class="screen-reader-text">Close modal window</span>
            </a>
            <div class="video-wrap">
                <iframe id="weforms-upsell-modal-iframe" width="1280" height="720" src="" frameborder="0" allowfullscreen></iframe>
            </div>

            <div class="learn-more">
                <a href="<?php echo esc_url( $this->learn_more_link() ); ?>" target="_blank" class="button button-primary">Learn more about weForms</a>
            </div>
        </div>
        <div class="weforms-upsell-modal-backdrop" id="weforms-upsell-modal-backdrop"></div>

        <style type="text/css">
            div#weforms-upsell-prompt * {
                box-sizing: border-box;
            }

            div#weforms-upsell-prompt {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: flex-start;
                align-content: flex-start;
                align-items: flex-start;
                position: relative;
                border: none;
                margin: 5px 0 15px;
                padding: 0 0 0 10px;
            }

            .weforms-upsell-logo {
                margin: 0;
                height: 71px;
                order: 0;
                flex: 0 1 272px;
                align-self: auto;
                padding-left: 15px;
            }

            .weforms-upsell-text {
                background: #46b450;
                color: #fff;
                padding: 0;
                height: 71px;
                margin-left: -35px;
                order: 0;
                flex: 1 1 auto;
                align-self: auto;
            }

            .weforms-upsell-text h2 {
                color: #fff;
                margin: 10px 0;
            }

            .weforms-upsell-cta {
                text-align: center;
                order: 0;
                flex: 0 1 220px;
                align-self: auto;
                padding-top: 20px;
                vertical-align: middle;
                height: 71px;
                line-height: 28px;
            }

            .weforms-upsell-modal {
                background: #fff;
                position: fixed;
                top: 5%;
                bottom: 5%;
                right: 10%;
                left: 10%;
                display: none;
                box-shadow: 0 1px 20px 5px rgba(0, 0, 0, 0.1);
                z-index: 160000;
            }

            .weforms-upsell-modal .video-wrap {
                position: relative;
                padding-bottom: 56.25%; /* 16:9 */
                padding-top: 25px;
                height: 0;
            }

            .weforms-upsell-modal .video-wrap iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }

            .weforms-upsell-modal .learn-more {
                position: absolute;
                bottom: 0;
                right: 10px;
                background: #fff;
                padding: 10px;
                border-radius: 3px;
            }

            .weforms-upsell-modal a.close {
                position: absolute;
                top: 20px;
                right: -60px;
                font: 300 1.71429em "dashicons" !important;
                content: '\f335';
                display: inline-block;
                padding: 10px 20px 0 20px;
                z-index: 5;
                text-decoration: none;
                height: 40px;
                cursor: pointer;
                background: #000;
                color: #fff;
                border-radius: 50%;
            }

            .weforms-upsell-modal-backdrop {
                position: fixed;
                z-index: 159999;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                min-height: 360px;
                background: #000;
                opacity: .7;
                display: none;
            }

            .weforms-upsell-modal.show,
            .weforms-upsell-modal-backdrop.show {
                display: block;
            }
        </style>

        <script type="text/javascript">
            (function ($) {
                var wrapper = $('#weforms-upsell-prompt'),
                    modal = $('#weforms-upsell-modal'),
                    modalBackdrop = $('#weforms-upsell-modal-backdrop'),
                    iframe = $('#weforms-upsell-modal-iframe');

                wrapper.on('click', '#weforms-upsell-prompt-btn', function (e) {
                    var self = $(this);

                    e.preventDefault();
                    self.addClass('install-now updating-message');
                    self.text('<?php echo esc_js( 'Installing...' ); ?>');

                    wp.ajax.send( 'weforms_upsell_installer', {
                        data: {
                            _wpnonce: '<?php echo esc_html( wp_create_nonce('weforms_upsell_installer') ); ?>'
                        },

                        success: function(response) {
                            self.text('<?php echo esc_js( 'Installed' ); ?>');
                            window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=weforms' ) ); ?>';
                        },

                        error: function(error) {
                            self.removeClass('install-now updating-message');
                            alert( error );
                        },

                        complete: function() {
                            self.attr('disabled', 'disabled');
                            self.removeClass('install-now updating-message');
                        }
                    });
                });

                wrapper.on('click', '.notice-dismiss', function (e) {
                    e.preventDefault();

                    var self = $(this);

                    wp.ajax.send( 'weforms_upsell_dismiss' );

                    self.closest('.updated').slideUp('fast', function() {
                        self.remove();
                    });
                });

                wrapper.on('click', 'a.learn-more', function(e) {
                    e.preventDefault();

                    modal.addClass('show');
                    modalBackdrop.addClass('show');

                    iframe.attr( 'src', 'https://www.youtube.com/embed/sqP-nvyqUdQ?rel=0&amp;controls=0&amp;showinfo=0&amp;autoplay=1' );
                });

                $('body').on('click', '.weforms-upsell-modal a.close', function(e) {
                    e.preventDefault();

                    console.log('close modal');

                    modal.removeClass('show');
                    modalBackdrop.removeClass('show');

                    iframe.attr( 'src', '' );
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Check if the notice is dimissed
     *
     * @return boolean
     */
    public function is_dismissed() {
        return 'yes' == get_option( 'weforms_upsell_dismiss', 'no' );
    }

    /**
     * Dismiss the weForms notice
     *
     * @return void
     */
    public function dismiss_notice() {
        update_option( 'weforms_upsell_dismiss', 'yes' );
    }

    /**
     * Learn more link, append affiliate link if present
     *
     * @return string
     */
    public function learn_more_link() {
        $link = $this->learn_more;

        if ( ! empty( $this->affiliate ) ) {
            $link = add_query_arg( array( 'ref' => $this->affiliate ), $link );
        }

        return $link;
    }

    /**
     * Fail if plugin installtion/activation fails
     *
     * @param  Object $thing
     *
     * @return void
     */
    public function fail_on_error( $thing ) {
        if ( is_wp_error( $thing ) ) {
            wp_send_json_error( $thing->get_error_message() );
        }
    }

    /**
     * Install weForms
     *
     * @return void
     */
    public function install_weforms() {
        check_ajax_referer( 'weforms_upsell_installer' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'You don\'t have permission to install the plugins' ) );
        }

        if ( ! class_exists( 'WP_User_Frontend' ) ) {
            $wpuf_status = $this->install_plugin( 'wp-user-frontend', 'wpuf.php' );

            $this->fail_on_error( $wpuf_status );
        }

        $weforms_status = $this->install_plugin( 'weforms', 'weforms.php' );
        $this->fail_on_error( $weforms_status );

        $this->dismiss_notice();

        if ( ! empty( $this->affiliate ) ) {
            update_option( '_weforms_aff_ref', $this->affiliate );
        }

        wp_send_json_success();
    }

    /**
     * Install and activate a plugin
     *
     * @param  string $slug
     * @param  string $file
     *
     * @return WP_Error|null
     */
    public function install_plugin( $slug, $file ) {
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin_basename = $slug . '/' . $file;

        // if exists and not activated
        if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_basename ) ) {
            return activate_plugin( $plugin_basename );
        }

        // seems like the plugin doesn't exists. Download and activate it
        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

        $api      = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
        $result   = $upgrader->install( $api->download_link );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return activate_plugin( $plugin_basename );
    }

    /**
     * Dismiss the notice via Ajax
     *
     * @return void
     */
    public function dismiss_weforms_notice() {
        $this->dismiss_notice();

        wp_send_json_success();
    }
}

endif;
