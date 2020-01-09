<?php
/**
 * Email Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        </table>

                        <!-- Footer -->
                        <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
                            <tr>
                                <td valign="top">
                                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                        <tr>
                                            <td colspan="2" valign="middle" id="credit">
                                                <?php
                                                $settings = get_option( 'erp_settings_erp-email_general', [] );
                                                if ( isset( $settings['footer_text'] ) && !empty( $settings['footer_text'] ) ) {
                                                    $footer_text = $settings['footer_text'];
                                                }

                                                $footer_text = empty( $footer_text ) ? sprintf( '&copy; %s', get_bloginfo( 'name', 'display' ) ) : $footer_text;
                                                echo wp_kses_post( wpautop( wptexturize( apply_filters( 'erp_email_footer_text', $footer_text ) ) ) );

                                                do_action( 'erp_email_after_footer' );
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <!-- End Footer -->
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
