<?php
namespace WeDevs\ERP\Admin;

use WeDevs\ERP\Admin\Models\Company_Locations;
use WeDevs\ERP\Company;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * The ajax handler class
 *
 * Handles the requests from core ERP, not from modules
 */
class Ajax {

    use \WeDevs\ERP\Framework\Traits\Ajax;
    use Hooker;

    /**
     * Bind events
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp-company-location', 'location_create');
        $this->action( 'wp_ajax_erp-delete-comp-location', 'location_remove');
        $this->action( 'wp_ajax_erp_audit_log_view', 'view_edit_log_changes');
        $this->action( 'wp_ajax_erp_file_upload', 'file_uploader' );
        $this->action( 'wp_ajax_erp_file_del', 'file_delete' );
        $this->action( 'wp_ajax_erp_activation_notice', 'erp_activation_notice_callback' );
        $this->action( 'wp_ajax_erp_people_exists', 'check_people' );
        $this->action( 'wp_ajax_erp_smtp_test_connection', 'smtp_test_connection' );
        $this->action( 'wp_ajax_erp_imap_test_connection', 'imap_test_connection' );
    }

    function file_delete() {
        $this->verify_nonce( 'erp-nonce' );

        $attach_id = isset( $_POST['attach_id'] ) ? $_POST['attach_id'] : 0;
        $custom_attr = isset( $_POST['custom_attr'] ) ? $_POST['custom_attr'] : [];
        $upload    = new \WeDevs\ERP\Uploader();

        if ( is_array( $attach_id) ) {
            foreach ( $attach_id as $id ) {
                do_action( 'erp_before_delete_file', $id, $custom_attr );
                $delete = $upload->delete_file( $id );
            }
        } else {
            do_action( 'erp_before_delete_file', $attach_id, $custom_attr );
            $delete = $upload->delete_file( intval( $attach_id ) );
        }

        if ( $delete ) {
            $this->send_success();
        } else {
            $this->send_error();
        }
    }

    /**
     * Upload a new file
     *
     * @return void
     */
    function file_uploader() {
        $this->verify_nonce( 'erp-nonce' );
        $upload = new \WeDevs\ERP\Uploader();
        $file   = $upload->upload_file();
        $this->send_success( $file );
    }

    /**
     * Create a new company location
     *
     * @return void
     */
    public function location_create() {
        $this->verify_nonce( 'erp-company-location' );

        $location_name = isset( $_POST['location_name'] ) ? sanitize_text_field( $_POST['location_name'] ) : '';
        $address_1     = isset( $_POST['address_1'] ) ? sanitize_text_field( $_POST['address_1'] ) : '';
        $address_2     = isset( $_POST['address_2'] ) ? sanitize_text_field( $_POST['address_2'] ) : '';
        $city          = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
        $state         = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
        $zip           = isset( $_POST['zip'] ) ? intval( $_POST['zip'] ) : '';
        $country       = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
        $location_id   = isset( $_POST['location_id'] ) ? intval( $_POST['location_id'] ) : 0;

        $args = [
            'id'         => $location_id,
            'name'       => $location_name,
            'address_1'  => $address_1,
            'address_2'  => $address_2,
            'city'       => $city,
            'state'      => $state,
            'zip'        => $zip,
            'country'    => $country
        ];

        $company = new Company();
        $location_id = $company->create_location( $args );

        if ( is_wp_error( $location_id ) ) {
            $this->send_error( $location_id->get_error_message() );
        }

        $this->send_success( array( 'id' => $location_id, 'title' => $location_name ) );
    }

    /**
     * Remove a location
     *
     * @return void
     */
    public function location_remove() {
        $this->verify_nonce( 'erp-nonce' );

        $location_id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $location_id ) {
            Company_Locations::find( $location_id )->delete();
        }

        $this->send_success();
    }

    public function view_edit_log_changes() {

        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $log_id = intval( $_POST['id'] );

        if ( ! $log_id ) {
            $this->send_error();
        }

        $log = \WeDevs\ERP\Admin\Models\Audit_Log::find( $log_id );
        $old_value = maybe_unserialize( base64_decode( $log->old_value ) );
        $new_value = maybe_unserialize( base64_decode( $log->new_value ) );
        ob_start();
        ?>
        <div class="wrap">
            <table class="wp-list-table widefat fixed audit-log-change-table">
                <thead>
                    <tr>
                        <th class="col-date"><?php _e( 'Field/Items', 'erp' ); ?></th>
                        <th class="col"><?php _e( 'Old Value', 'erp' ); ?></th>
                        <th class="col"><?php _e( 'New Value', 'erp' ); ?></th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th class="col-items"><?php _e( 'Field/Items', 'erp' ); ?></th>
                        <th class="col"><?php _e( 'Old Value', 'erp' ); ?></th>
                        <th class="col"><?php _e( 'New Value', 'erp' ); ?></th>
                    </tr>
                </tfoot>

                <tbody>
                    <?php $i=1; ?>
                    <?php foreach( $old_value as $key => $value ) { ?>
                        <tr class="<?php echo $i % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td class="col-date"><?php echo ucfirst( str_replace('_', ' ', $key ) ); ?></td>
                            <td><?php echo ( $value ) ? stripslashes( $value ) : '--'; ?></td>
                            <td><?php echo ( $new_value[$key] ) ? stripslashes( $new_value[$key] ) : '--'; ?></td>
                        </tr>
                    <?php $i++; } ?>
                </tbody>
            </table>
        </div>
        <?php
        $content = ob_get_clean();

        $data = [
            'title' => __( 'Log changes', 'erp' ),
            'content' => $content
        ];

        $this->send_success( $data );
    }

    /**
     * Handle erp activation ajax request.
     *
     * @return void
     */
    public function erp_activation_notice_callback() {
        $this->verify_nonce( 'wp-erp-activation-nonce' );

        if ( isset( $_POST['dismiss'] ) ) {
            update_option( 'wp_erp_activation_dismiss', true );

            $this->send_success();
        }

        if ( isset( $_POST['email'] ) ) {
            $email      = $_POST['email'];
            $site_url   = site_url();

            $response = wp_remote_get( 'http://api.wperp.com/apikey?email=' . $email . '&site_url=' . $site_url  );

            if ( is_array( $response ) ) {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );

                if ( isset( $body['apikey'] ) ) {
                    update_option( 'wp_erp_apikey', $body['apikey'] );
                    update_option( 'wp_erp_api_active', $body['status'] );

                    if ( isset( $body['email_count'] ) ) {
                        update_option( 'wp_erp_cloud_email_count', $body['email_count'] );
                    }

                    $this->send_success();
                } else {
                    $this->send_error( $body );
                }
            }
        }

        if ( isset( $_POST['disconnect'] ) ) {
            delete_option( 'wp_erp_activation_dismiss' );
            delete_option( 'wp_erp_apikey' );
            delete_option( 'wp_erp_api_active' );

            $this->send_success();
        }
    }

    /**
     * Check if a people exists
     *
     * @return void
     */
    public function check_people() {
        $email = isset( $_REQUEST['email'] ) ? sanitize_text_field( $_REQUEST['email'] ) : false;

        if ( ! $email ) {
            $this->send_error( __( 'No email address provided', 'erp' ) );
        }

        $user = \get_user_by( 'email', $email );

        if ( false === $user ) {
            $people = erp_get_people_by( 'email', $email );
        } else {
            $peep = \WeDevs\ERP\Framework\Models\People::with('types')->whereUserId( $user->ID )->first();

            if ( null === $peep ) {
                $this->send_success();
            } else {
                $people        = (object) $peep->toArray();
                $people->types = wp_list_pluck( $peep->types->toArray(), 'name' );
            }
        }

        // we didn't found any user with this email address
        if ( false === $people ) {
            $this->send_success();
        }

        // seems like we found one
        $this->send_error( $people );
    }

    /**
     * Test the SMTP connection.
     *
     * @return void
     */
    public function smtp_test_connection() {
        $this->verify_nonce( 'erp-smtp-test-connection-nonce' );

        if ( empty( $_REQUEST['mail_server'] ) ) {
            $this->send_error( __( 'No host address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['port'] ) ) {
            $this->send_error( __( 'No port address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['username'] ) ) {
            $this->send_error( __( 'No email address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['password'] ) ) {
            $this->send_error( __( 'No email password provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['to'] ) ) {
            $this->send_error( __( 'No testing email address provided', 'erp' ) );
        }

        $mail_server = $_REQUEST['mail_server'];
        $port = isset( $_REQUEST['port'] ) ? $_REQUEST['port'] : 465;
        $encryption = isset( $_REQUEST['encryption'] ) ? $_REQUEST['encryption'] : 'ssl';
        $authentication = ( $_REQUEST['authentication'] == 'yes' ) ? true : false;
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];

        global $phpmailer;

        if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
            require_once ABSPATH . WPINC . '/class-smtp.php';
            $phpmailer = new \PHPMailer( true );
        }

        $to      = $_REQUEST['to'];
        $subject = __( 'ERP SMTP Test Mail', 'erp' );
        $message = __( 'This is a test email by WP ERP.', 'erp' );

        $phpmailer->AddAddress( $to );
        $phpmailer->Subject    = $subject;
        $phpmailer->Body       = $message;
        $phpmailer->FromName   = 'WP ERP';
        $phpmailer->Mailer     = 'smtp';
        $phpmailer->Host       = $mail_server;
        $phpmailer->SMTPSecure = $encryption;
        $phpmailer->Port       = $port;
        $phpmailer->SMTPAuth   = $authentication;

        if ( $phpmailer->SMTPAuth ) {
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
        }

        // $phpmailer->SMTPDebug = true;
        try {
            $result = $phpmailer->Send();

            $this->send_success( __( 'Test email has been sent.', 'erp' ) );
        } catch( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Test the Imap connection.
     *
     * @return void
     */
    public function imap_test_connection() {
        $this->verify_nonce( 'erp-imap-test-connection-nonce' );

        if ( empty( $_REQUEST['mail_server'] ) ) {
            $this->send_error( __( 'No host address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['username'] ) ) {
            $this->send_error( __( 'No email address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['password'] ) ) {
            $this->send_error( __( 'No email password provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['port'] ) ) {
            $this->send_error( __( 'No port address provided', 'erp' ) );
        }

        $mail_server = $_REQUEST['mail_server'];
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $protocol = $_REQUEST['protocol'];
        $port = isset( $_REQUEST['port'] ) ? $_REQUEST['port'] : 993;
        $encryption = isset( $_REQUEST['encryption'] ) ? $_REQUEST['encryption'] : 'ssl';
        $certificate = ( $_REQUEST['certificate'] == 1 ) ? true : false;

        try {
            $imap = new \WeDevs\ERP\Imap( $mail_server, $port, $protocol, $username, $password, $encryption, $certificate );
            $imap->is_connected();

            $this->send_success( __( 'Your IMAP connection is established.', 'erp' ) );
        } catch( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }
}

new Ajax();
