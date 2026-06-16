<?php

namespace WeDevs\ERP;

/**
 * Promotional offer class
 */
class Promotion {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'promotional_offer' ] );
        add_action( 'wp_ajax_erp-dismiss-promotional-offer-notice-temp', array( $this, 'dismiss_promotional_offer' ) );
    }

     /**
     * Get prmotion data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function promotional_offer() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        // Don't show if ERP Pro exists
        if ( defined( 'ERP_PRO_PLUGIN_VERSION' ) ) {
            return;
        }
        // Check if inside the wp-project-manager page
        if ( ! isset( $_GET['page'] ) ) {
            return;
        }

        $offer = $this->get_offer();
        if ( ! $offer->status ) {
            return;
        }

        ?>
            <style>
                #wperp-notice .content {
                    display: flex;
                    /* align-items: center; */
                }

                .wperp-promotional-offer-notice {
                    background: linear-gradient(30deg, #f2f2f2, #4a90e2);
                    color: #444;
                    border-left: 5px solid #4a90e2;
                }

                .wperp-promotional-offer-notice p {
                    font-size: 16px;
                    font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
                    color: #444;
                }

                .wperp-promotional-offer-notice a {
                    color: #fff;
                    display: inline-block;
                    margin-top: 18px;
                    border: 0.5px solid #4a90e2;
                    border-radius: 3px;
                    padding: 2px 5px 1px 5px;
                    text-decoration: none;
                    font-size: 16px;
                    padding: 4px 10px;
                    font-weight: 300;
                    background: #4a90e2;
                    /* font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; */
                }

                .wperp-promotional-offer-notice a:hover {
                    color: #fff;
                    border: 0.5px solid #357abd;
                    background: #357abd;
                }
                .welcome-panel .welcome-panel-close:before, .tagchecklist .ntdelbutton .remove-tag-icon:before, #bulk-titles .ntdelbutton:before, .notice-dismiss:before{
                    color:white;
                }
            </style>

            <div class="notice notice-success is-dismissible wperp-promotional-offer-notice" id="wperp-notice">
                <div class="content">
                <p style="margin-right:14px ;">
                        <img height="100" src="https://ps.w.org/erp/assets/icon-256x256.gif?rev=2818774" alt="">
                </p>
                <p>
                        <?php echo wp_kses( $offer->message, [ 'strong' => [], 'br' => [] ] ); ?>
                        <br>
                        <a class="link" target="_blank" href="<?php echo esc_url( $offer->link ); ?>">
                            <?php printf( esc_html__( '%s', 'erp' ), $offer->btn_txt ); ?>
                        </a>
                    </p>

                </div>
            </div>

            <script type='text/javascript'>

                jQuery('body').on('click', '#wperp-notice .notice-dismiss', function(e) {
                    e.preventDefault();

                    jQuery.ajax({
                        type: 'POST',
                        data: {
                            action: 'erp_dismiss_offer',
                            nonce: '<?php echo esc_attr( wp_create_nonce( 'wperp-dismiss-offer-notice' ) ); ?>',
                            wperp_offer_key: '<?php echo esc_attr( $offer->key ); ?>'
                        },
                        url: '<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>',
                        success: function (res) {

                        }
                    });
                });
            </script>
        <?php
    }

    /**
     * Generate offer notice
     *
     * @since 1.6.9
     *
     * @return void
     */
    public function generate_notice( $offer ) {
        // check if it has already been dismissed
        if ( 'hide' === get_option( $offer->key, 'no' ) ) {
            return;
        }

        ?>
        <div class="notice is-dismissible erp-promotional-offer-notice" id="erp-promotional-offer-notice">
            <p class="highlight-text">
                <?php echo wp_kses( $offer->message, [ 'strong' => [], 'br' => [] ] ); ?>
                <p>
                    <a target="_blank"
                        href="<?php echo esc_url_raw( $offer->link ) ?>"
                        style="padding:5px 15px;">
                        <?php echo esc_html( $offer->btn_txt ); ?>
                    </a>
                </p>
            </p>
        </div><!-- #erp-promotional-offer-notice -->

        <script type='text/javascript'>
            jQuery('body').on('click', '#erp-promotional-offer-notice .notice-dismiss', function(e) {
                e.preventDefault();

                wp.ajax.post('erp-dismiss-promotional-offer-notice-temp', {
                    dismissed   : true,
                    option_name : '<?php echo esc_attr( $offer->key ); ?>',
                    _wpnonce    : '<?php echo esc_attr( wp_create_nonce( 'erp_admin' ) ); ?>'
                } );
            });
        </script>

        <style>
            .erp-promotional-offer-notice {
                background: linear-gradient(30deg, #f2f2f2, lightseagreen);
                color: rgba(22, 134, 129, 1.1);
                border-left: 5px solid lightseagreen;
            }

            .erp-promotional-offer-notice p {
                font-size: 16px;
                font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            }

            .erp-promotional-offer-notice a {
                color: lightcyan;
                border: 0.5px solid lightseagreen;
                border-radius: 3px;
                padding: 2px 5px 1px 5px;
                text-decoration: none;
                font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
                font-size: 16px;
                font-weight: 300;
                background: lightseagreen;
            }

            .erp-promotional-offer-notice a:hover {
                color: white;
                border: 0.5px solid rgba(44, 187, 180, 0.897);
                background: rgba(44, 187, 180, 0.897);
            }
        </style>
        <?php
    }


     /**
     * Retrieves offer data.
     *
     * @return object
     */
    public function get_offer() {
        $offer         = new \stdClass;
        $offer->status = false;
        $promo_notice  = get_transient( 'wperp_promo_notice' );

        if ( false === $promo_notice ) {
            $promo_notice_url = 'https://raw.githubusercontent.com/wp-erp/erp-utils/refs/heads/main/promotions.json';
            $response         = wp_remote_get( $promo_notice_url, array( 'timeout' => 15 ) );

            if ( is_wp_error( $response ) || $response['response']['code'] !== 200 ) {
                return $offer;
            }

            $promo_notice = wp_remote_retrieve_body( $response );
            set_transient( 'wperp_promo_notice', $promo_notice, 6 * HOUR_IN_SECONDS );
        }

        $promo_notice = json_decode( $promo_notice, true );
        $current_time = new \DateTimeImmutable( 'now', new \DateTimeZone('America/New_York') );
        $current_time = $current_time->format( 'Y-m-d H:i:s T' );
        $disabled_key = get_option( 'wperp_offer_notice' );

        if ( $current_time >= $promo_notice['start_date'] && $current_time <= $promo_notice['end_date'] ) {
            $offer->link      = $promo_notice['action_url'];
            $offer->key       = $promo_notice['key'];
            $offer->btn_txt   = ! empty( $promo_notice['action_title'] ) ? $promo_notice['action_title'] : 'Get Now';
            $offer->message   = [];
            $offer->message[] = sprintf( __( '<strong>%s</strong>', 'erp' ), $promo_notice['title'] );

            if ( ! empty( $promo_notice['description'] ) ) {
                $offer->message[] = sprintf( __( '%s', 'erp' ), $promo_notice['description'] );
            }

            $offer->message[] = sprintf( __( '%s', 'erp' ), $promo_notice['content'] );
            $offer->message   = implode( '<br>', $offer->message );

            if ( $disabled_key != $promo_notice['key'] ) {
                $offer->status = true;
            }
        }

        return $offer;
    }


    /**
     * Dismiss promotion notice
     *
     * @since  2.5
     *
     * @return void
     */
    public function dismiss_promotional_offer() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'erp' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( esc_html__( 'You have no permission to do that', 'erp' ) );
        }

        if ( ! empty( $_POST['dismissed'] ) ) {
            $offer_key = ! empty( $_POST['option_name'] ) ? sanitize_text_field( wp_unslash( $_POST['option_name'] ) ) : '';
            update_option( $offer_key, 'hide' );
        }
    }
}
