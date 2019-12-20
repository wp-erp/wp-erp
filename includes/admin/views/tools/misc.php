<div class="postbox">
    <div class="inside">
        <h3><?php esc_html_e( 'Send Test Email', 'erp' ); ?></h3>

        <?php
        $email_settings = get_option( 'erp_settings_erp-email_general', [] );

        if ( isset( $_GET['sent'] ) ) {
            erp_html_show_notice(  esc_html__( 'The test email has been sent by WordPress. Please note this does NOT mean it has been delivered.', 'erp' ) );
        }
        ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=erp-tools&tab=misc' ) ); ?>" id="erp-test-email-form">

            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="to"><?php esc_html_e( 'To', 'erp' ); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <?php erp_html_form_input([
                                'type'        => 'email',
                                'name'        => 'to',
                                'value'       => wp_get_current_user()->user_email,
                                'placeholder' => 'recipient@domain.com',
                                'custom_attr' => [
                                    'size' => 40
                                ]
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="from"><?php esc_html_e( 'From', 'erp' ); ?></label>
                        </th>
                        <td>
                            <?php
                                global $current_user;

                                $from_name  = ( ! empty( $email_settings['from_name'] ) ) ? $email_settings['from_name'] : $current_user->display_name;
                                $from_email = ( ! empty( $email_settings['from_email'] ) ) ? $email_settings['from_email'] : get_option( 'admin_email' );

                                erp_html_form_input([
                                    'type'        => 'text',
                                    'name'        => 'from',
                                    'value'       => sprintf( '%s <%s>', esc_html( $from_name ), esc_html( $from_email ) ),
                                    'custom_attr' => [
                                        'readonly' => 'readonly',
                                        'size'     => 40
                                    ]
                                ]);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="body"><?php esc_html_e( 'Message', 'erp' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input([
                                'type'        => 'textarea',
                                'name'        => 'body',
                                'placeholder' => esc_html__( 'Leave blank to send default texts', 'erp' ),
                                'custom_attr' => [
                                    'cols' => 45,
                                    'rows' => 6
                                ]
                            ]); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php wp_nonce_field( 'erp-test-email-nonce' ); ?>
            <?php submit_button( __( 'Send Email', 'erp' ), 'primary', 'erp_send_test_email' ); ?>
        </form>
    </div><!-- .inside -->
</div><!-- .postbox -->
