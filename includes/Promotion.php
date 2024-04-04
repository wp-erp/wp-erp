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

        if ( ! isset( $_GET['page'] ) || 0 !== strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'erp' ) ) {
            return;
        }

        $promo_notice  = get_transient( 'erp_promo_notice' );

        if ( false === $promo_notice ) {
            $promo_notice_url = 'http://wperp.com/wp-json/erp/v1/promotions';
            $response         = wp_remote_get( $promo_notice_url, [ 'timeout' => 15 ] );

            if ( is_wp_error( $response ) || $response['response']['code'] !== 200 ) {
                return;
            }

            $promo_notice = wp_remote_retrieve_body( $response );
            set_transient( 'erp_promo_notice', $promo_notice, DAY_IN_SECONDS );
        }

        $promo_notice = json_decode( $promo_notice, true );
        $current_time = erp_current_datetime()->setTimezone( new \DateTimeZone( 'America/New_York' ) )->format( 'Y-m-d H:i:s T' );

        if ( $current_time > $promo_notice['end_date'] || $current_time < $promo_notice['start_date'] ) {
            return;
        }

        $offer            = new \stdClass;
        $offer->link      = $promo_notice['action_url'];
        $offer->key       = "erp-{$promo_notice['key']}";
        $offer->btn_txt   = ! empty( $promo_notice['action_title'] ) ? $promo_notice['action_title'] : __( 'Get Now', 'erp' );
        $offer->message   = [];
        $offer->message[] = sprintf( __( '<strong>%s</strong>', 'erp' ), $promo_notice['title'] );

        if ( ! empty( $promo_notice['description'] ) ) {
            $offer->message[] = sprintf( __( '%s', 'erp' ), $promo_notice['description'] );
        }

        $offer->message[] = sprintf( __( '%s', 'erp' ), $promo_notice['content'] );
        $offer->message   = implode( '<br>', $offer->message );

        return $this->generate_notice( $offer );
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
