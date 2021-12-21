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

        $current_time      = erp_current_datetime()->setTimezone ( new \DateTimeZone( 'America/New_York' ) );
        $promotion_start   = $current_time->setDate( 2021, 12, 24 )->setTime( 9, 0, 0 );
        $promotion_end     = $current_time->setDate( 2022, 01, 12 )->setTime( 23, 59, 59 );

        // 2021-03-15 09:00:00 EST - 2021-03-22 23:59:59 EST
        if ( $current_time > $promotion_end || $current_time < $promotion_start ) {
            return;
        }

        if ( $current_time >= $promotion_start && $current_time <= $promotion_end ) {
            $msg            = 'Happy Holidays.<br>Let The Festivities Begin.<br>Enjoy Up To <strong>35% OFF</strong> on <strong>WP ERP Pro.</strong>';
            $option_name    = 'erp_holidays_offer_2021';

            $this->generate_notice( $msg, $option_name );
            return;
        }
    }

    /**
     * Generate offer notice
     *
     * @since 1.6.9
     *
     * @return void
     */
    public function generate_notice( $msg, $option_name ) {
        // check if it has already been dismissed
        $hide_notice = get_option( $option_name, 'no' );

        if ( 'hide' === $hide_notice ) {
            return;
        }

        $offer_msg = '<p class="highlight-text">' . sprintf( __( '%s', 'erp' ), $msg ) .
                '</p><p><a style="padding:5px 15px;" target="_blank" href="https://wperp.com/pricing/?nocache&utm_medium=text&utm_source=wordpress-erp-holidaysale2021">' . __( 'Get Now', 'erp' ) . '</a></p>';
        ?>
        <div class="notice is-dismissible erp-promotional-offer-notice" id="erp-promotional-offer-notice">
            <p class="highlight-text">
                <?php printf( __( '%s', 'erp' ), $msg ); ?>
                <a target="_blank"
                    href="https://wperp.com/pricing/?nocache&utm_medium=text&utm_source=wordpress-erp-holidaysale2021"
                    style="padding:5px 15px;">
                    <?php esc_html_e( 'Get Now', 'erp' ); ?>
                </a>
            </p>
        </div><!-- #erp-promotional-offer-notice -->

        <script type='text/javascript'>
            jQuery('body').on('click', '#erp-promotional-offer-notice .notice-dismiss', function(e) {
                e.preventDefault();

                wp.ajax.post('erp-dismiss-promotional-offer-notice-temp', {
                    dismissed   : true,
                    option_name : '<?php echo esc_html( $option_name ); ?>',
                    _wpnonce    : '<?php echo wp_create_nonce( 'erp_admin' ); ?>',
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
