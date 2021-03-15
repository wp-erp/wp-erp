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
        
        $current_time      = erp_current_datetime()->setTimezone ( new \DateTimeZone( 'America/New_York' ) );
        $promotion_start   = $current_time->setDate( 2021, 03, 15 )->setTime( 9, 0, 0 );
        $promotion_end     = $promotion_start->modify( '+7 days' )->setTime( 23, 59, 59 );
        
        // 2021-03-15 09:00:00 EST - 2021-03-22 23:59:59 EST
        if ( $current_time > $promotion_end || $current_time < $promotion_start ) {
            return;
        }

        if ( $current_time >= $promotion_start && $current_time <= $promotion_end ) {
            $msg            = 'It\'s Our Birthday! Enjoy Up To  45% OFF  on WP ERP Pro.';
            $option_name    = 'erp_wedevs_birthday_2021';
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

        $offer_msg = '<p><strong class="highlight-text">' . $msg . '</strong>' . 
                ' <a target="_blank" href="https://wperp.com/pricing/?nocache&utm_medium=text&utm_source=wordpress-erp-holidays">Get Now</a>
            </p>';
        ?>
        <div class="notice is-dismissible erp-promotional-offer-notice" id="erp-promotional-offer-notice">
            <?php echo wp_kses_post( $offer_msg ); ?>
        </div><!-- #erp-promotional-offer-notice -->

        <script type='text/javascript'>
            jQuery('body').on('click', '#erp-promotional-offer-notice .notice-dismiss', function(e) {
                e.preventDefault();

                wp.ajax.post('erp-dismiss-promotional-offer-notice-temp', {
                    dismissed: true,
                    option_name : '<?php echo esc_html( $option_name ); ?>',
                    _wpnonce : '<?php echo wp_create_nonce( 'erp_admin' ); ?>',
                } );
            });
        </script>

        <style>
            .erp-promotional-offer-notice {
                background: linear-gradient(45deg, #c03e3e, #fc9084);
                color: rgb(255, 255, 222);
                border-left: 6px solid rgb(252, 252, 198);
            }

            .erp-promotional-offer-notice p {
                font-size: 25px;
            }
            
            .erp-promotional-offer-notice a {
                color: rgb(250, 250, 208);
                border: 0.5px solid rgb(252, 252, 199);
                border-radius: 4px;
                padding: 3px;
                text-decoration: none;
                font-size: 25px;
                font-weight: 200;
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
