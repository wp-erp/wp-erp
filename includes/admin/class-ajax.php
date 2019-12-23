<?php
namespace WeDevs\ERP\Admin;

use WeDevs\ERP\Admin\Models\Company_Locations;
use WeDevs\ERP\Company;
use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\Models\APIKey;

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
        $this->action( 'wp_ajax_erp_people_exists', 'check_people' );
        $this->action( 'wp_ajax_erp_smtp_test_connection', 'smtp_test_connection' );
        $this->action( 'wp_ajax_erp_imap_test_connection', 'imap_test_connection' );
        $this->action( 'wp_ajax_erp_import_users_as_contacts', 'import_users_as_contacts' );
        $this->action( 'wp_ajax_erp-api-key', 'new_api_key');
        $this->action( 'wp_ajax_erp-api-delete-key', 'delete_api_key');
        $this->action( 'wp_ajax_erp-dismiss-promotional-offer-notice', 'dismiss_promotional_offer' );
    }

    function file_delete() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

        $attach_id   = isset( $_POST['attach_id'] ) ? sanitize_text_field( wp_unslash( $_POST['attach_id'] ) ) : 0;
        $custom_attr = isset( $_POST['custom_attr'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['custom_attr'] ) ) : [];
        $upload      = new \WeDevs\ERP\Uploader();

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
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

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
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-company-location' ) ) {
            return;
        }

        $location_name = isset( $_POST['location_name'] ) ? sanitize_text_field( wp_unslash( $_POST['location_name'] ) ) : '';
        $address_1     = isset( $_POST['address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['address_1'] ) ) : '';
        $address_2     = isset( $_POST['address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['address_2'] ) ) : '';
        $city          = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
        $state         = isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '';
        $zip           = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) )  : '';
        $country       = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
        $location_id   = isset( $_POST['location_id'] ) ? intval( wp_unslash( $_POST['location_id'] ) ) : 0;

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
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

        $location_id   = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;

        if ( $location_id ) {
            Company_Locations::find( $location_id )->delete();
        }

        $this->send_success();
    }

    public function view_edit_log_changes() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

        $log_id = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;

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
                        <th class="col-date"><?php esc_html_e( 'Field/Items', 'erp' ); ?></th>
                        <th class="col"><?php esc_html_e( 'Old Value', 'erp' ); ?></th>
                        <th class="col"><?php esc_html_e( 'New Value', 'erp' ); ?></th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th class="col-items"><?php esc_html_e( 'Field/Items', 'erp' ); ?></th>
                        <th class="col"><?php esc_html_e( 'Old Value', 'erp' ); ?></th>
                        <th class="col"><?php esc_html_e( 'New Value', 'erp' ); ?></th>
                    </tr>
                </tfoot>

                <tbody>
                    <?php $i=1; ?>
                    <?php foreach( $old_value as $key => $value ) { ?>
                        <tr class="<?php echo $i % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td class="col-date"><?php echo esc_html( ucfirst( str_replace('_', ' ', $key ) ) ); ?></td>
                            <td><?php echo ( $value ) ? esc_html( wp_unslash( $value ) ) : '--'; ?></td>
                            <td><?php echo ( $new_value[$key] ) ? esc_html( wp_unslash( $new_value[$key] ) ) : '--'; ?></td>
                        </tr>
                    <?php $i++; } ?>
                </tbody>
            </table>
        </div>
        <?php
        $content = ob_get_clean();

        $data = [
            'title' => esc_html__( 'Log changes', 'erp' ),
            'content' => $content
        ];

        $this->send_success( $data );
    }

    /**
     * Check if a people exists
     *
     * @return void
     */
    public function check_people() {
        $email = isset( $_REQUEST['email'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email'] ) ) : false;

        if ( ! $email ) {
            $this->send_error( esc_html__( 'No email address provided', 'erp' ) );
        }

        $user = \get_user_by( 'email', $email );

        if ( false === $user ) {
            $people = erp_get_people_by( 'email', $email );
        } else {
            $peep = \WeDevs\ERP\Framework\Models\People::with('types')->whereUserId( $user->ID )->first();

            if ( null === $peep ) {
                $user->data->types = 'wp_user';
                $people = $user;
            } else {
                $people        = (object) $peep->toArray();
                $people->types = wp_list_pluck( $peep->types->toArray(), 'name' );
            }
        }

        // we didn't found any user with this email address
        if ( !$people ) {
            $this->send_error();
        }

        // seems like we found one
        $this->send_success( $people );
    }

    /**
     * Test the SMTP connection.
     *
     * @return void
     */
    public function smtp_test_connection() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-smtp-test-connection-nonce' ) ) {
            return;
        }

        if ( empty( $_REQUEST['mail_server'] ) ) {
            $this->send_error( esc_html__( 'No host address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['port'] ) ) {
            $this->send_error( esc_html__( 'No port address provided', 'erp' ) );
        }

        if ( ! empty( $_REQUEST['authentication'] ) ) {
            if ( empty( $_REQUEST['username'] ) ) {
                $this->send_error( esc_html__( 'No email address provided', 'erp' ) );
            }

            if ( empty( $_REQUEST['password'] ) ) {
                $this->send_error( esc_html__( 'No email password provided', 'erp' ) );
            }
        }

        if ( empty( $_REQUEST['to'] ) ) {
            $this->send_error( esc_html__( 'No testing email address provided', 'erp' ) );
        }

        $mail_server    = isset( $_REQUEST['mail_server'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mail_server'] ) ) : '';
        $port           = isset( $_REQUEST['port'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['port'] ) ) : 465;
        $authentication = isset( $_REQUEST['authentication'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['authentication'] ) ) : 'smtp';
        $username       = isset( $_REQUEST['username'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['username'] ) ) : '';
        $password       = isset( $_REQUEST['password'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['password'] ) ) : '';

        global $phpmailer;

        if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
            require_once ABSPATH . WPINC . '/class-smtp.php';
            $phpmailer = new \PHPMailer( true );
        }

        $to      = isset( $_REQUEST['to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['to'] ) ) : '';
        $subject = esc_html__( 'ERP SMTP Test Mail', 'erp' );
        $message = esc_html__( 'This is a test email by WP ERP.', 'erp' );

        $erp_email_settings = get_option( 'erp_settings_erp-email_general', [] );

        if ( ! isset( $erp_email_settings['from_email'] ) ) {
            $from_email = get_option( 'admin_email' );
        } else {
            $from_email = $erp_email_settings['from_email'];
        }

        if ( ! isset( $erp_email_settings['from_name'] ) ) {
            global $current_user;

            $from_name = $current_user->display_name;
        } else {
            $from_name = $erp_email_settings['from_name'];
        }

        $content_type = 'text/html';

        $phpmailer->AddAddress( $to );
        $phpmailer->From       = $from_email;
        $phpmailer->FromName   = $from_name;
        $phpmailer->Sender     = $phpmailer->From;
        $phpmailer->Subject    = $subject;
        $phpmailer->Body       = $message;
        $phpmailer->Mailer     = 'smtp';
        $phpmailer->Host       = $mail_server;
        $phpmailer->SMTPSecure = $authentication;
        $phpmailer->Port       = $port;

        if ( ! empty( $_REQUEST['authentication'] ) ) {
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Username   = $username;
            $phpmailer->Password   = $password;
        }

        $phpmailer->isHTML(true);

        try {
            $result = $phpmailer->Send();

            $this->send_success( esc_html__( 'Test email has been sent.', 'erp' ) );
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
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-imap-test-connection-nonce' ) ) {
            return;
        }

        if ( empty( $_REQUEST['mail_server'] ) ) {
            $this->send_error( esc_html__( 'No host address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['username'] ) ) {
            $this->send_error( esc_html__( 'No email address provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['password'] ) ) {
            $this->send_error( esc_html__( 'No email password provided', 'erp' ) );
        }

        if ( empty( $_REQUEST['port'] ) ) {
            $this->send_error( esc_html__( 'No port address provided', 'erp' ) );
        }

        $mail_server    = isset( $_REQUEST['mail_server'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mail_server'] ) ) : '';
        $username       = isset( $_REQUEST['username'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['username'] ) ) : '';
        $password       = isset( $_REQUEST['password'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['password'] ) ) : '';
        $protocol       = isset( $_REQUEST['protocol'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['protocol'] ) ) : '';
        $port           = isset( $_REQUEST['port'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['port'] ) ) : 993;
        $authentication = isset( $_REQUEST['authentication'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['authentication'] ) ) : 'ssl';

        try {
            $imap = new \WeDevs\ERP\Imap( $mail_server, $port, $protocol, $username, $password, $authentication );
            $imap->is_connected();

            $this->send_success( esc_html__( 'Your IMAP connection is established.', 'erp' ) );
        } catch( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Import users as crm contacts.
     *
     * @since 1.1.2
     * @since 1.1.18 Introduce `ERP_IS_IMPORTING`
     * @since 1.1.19 Import partial data in case of existing contacts
     *
     * @return void
     */
    public function import_users_as_contacts() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-import-export-nonce' ) ) {
            return;
        }

        define( 'ERP_IS_IMPORTING' , true );

        $limit = 50; // Limit to import per request

        $attempt = get_option( 'erp_users_to_contacts_import_attempt', 1 );
        update_option( 'erp_users_to_contacts_import_attempt', $attempt + 1 );
        $offset = ( $attempt - 1 ) * $limit;

        $user_role     = isset( $_REQUEST['user_role'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_role'] ) ) : '';
        $contact_owner = isset( $_REQUEST['contact_owner'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contact_owner'] ) ) : '';
        $life_stage    = isset( $_REQUEST['life_stage'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['life_stage'] ) ) : '';
        $contact_group = isset( $_REQUEST['contact_group'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contact_group'] ) ) : '';

        if ( ! empty( $user_role ) ) {
            $user_query  = new \WP_User_Query( ['role__in' => $user_role, 'number' => $limit, 'offset' => $offset] );
            $users       = $user_query->get_results();
            $total_items = $user_query->get_total();
        } else {
            $user_query  = new \WP_User_Query( ['number' => $limit, 'offset' => $offset] );
            $users       = $user_query->get_results();
            $total_items = $user_query->get_total();
        }

        $user_ids = [];
        $user_ids = wp_list_pluck( $users, 'ID' );

        foreach ( $user_ids as $user_id ) {
            $wp_user     = get_user_by( 'id', $user_id );
            $phone       = get_user_meta( $user_id, 'phone', true );
            $street_1    = get_user_meta( $user_id, 'street_1', true );
            $street_2    = get_user_meta( $user_id, 'street_2', true );
            $city        = get_user_meta( $user_id, 'city', true );
            $state       = get_user_meta( $user_id, 'state', true );
            $postal_code = get_user_meta( $user_id, 'postal_code', true );
            $country     = get_user_meta( $user_id, 'country', true );

            $data = [
                'type'          => 'contact',
                'user_id'       => absint( $user_id ),
                'first_name'    => $wp_user->first_name,
                'last_name'     => $wp_user->last_name,
                'email'         => $wp_user->user_email,
                'phone'         => $phone,
                'street_1'      => $street_1,
                'street_2'      => $street_2,
                'city'          => $city,
                'state'         => $state,
                'postal_code'   => $postal_code,
                'country'       => $country,
                'contact_owner' => $contact_owner,
                'life_stage'    => $life_stage
            ];

            $people = erp_insert_people( $data, true );

            if ( is_wp_error( $people ) ) {
                continue;
            } else {
                $contact = new \WeDevs\ERP\CRM\Contact( absint( $people->id ), 'contact' );

                if ( ! $people->exists) {
                    $contact->update_life_stage($life_stage);
                    $contact->update_contact_owner( $contact_owner );

                } else {
                    if ( ! $contact->get_life_stage() ) {
                        $contact->update_life_stage( $life_stage );
                    }

                    if ( ! $contact->get_contact_owner() ) {
                        $contact->update_contact_owner( $contact_owner );
                    }
                }

                $existing_data = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( [ 'group_id' => $contact_group, 'user_id' => $people->id ] )->first();

                if ( empty( $existing_data ) ) {
                    $hash = sha1( microtime() . 'erp-subscription-form' . $contact_group . $people->id );

                    erp_crm_create_new_contact_subscriber([
                        'group_id'          => $contact_group,
                        'user_id'           => $people->id,
                        'status'            => 'subscribe',
                        'subscribe_at'      => current_time( 'mysql' ),
                        'unsubscribe_at'    => null,
                        'hash'              => $hash
                    ]);
                }
            }
        }

        // re-calculate stats
        if ( $total_items <= ( $attempt * $limit ) ) {
            $left = 0;
        } else {
            $left = $total_items - ( $attempt * $limit );
        }

        if ( $left === 0 ) {
            delete_option( 'erp_users_to_contacts_import_attempt' );
        }

        $this->send_success( [ 'left' => $left, 'total_items' => $total_items, 'exists' => 0 ] );
    }

    /**
     * New api key
     *
     * @return void
     */
    public function new_api_key() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-api-key' ) ) {
            return;
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            $api_key = \WeDevs\ERP\Framework\Models\APIKey::find( $id );

            $api_key->update( [
                'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
                'user_id' => isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0
            ] );

            $this->send_success( $api_key );
        }

        $api_key = [
            'name'       => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
            'api_key'    => 'ck_' . esc_html( erp_generate_key() ),
            'api_secret' => 'cs_' . esc_html( erp_generate_key() ),
            'user_id'    => intval( $_POST['user_id'] ),
            'created_at' => current_time( 'mysql' ),
        ];

        $data = \WeDevs\ERP\Framework\Models\APIKey::create( $api_key );

        $this->send_success( $data );
    }

    /**
     * Delete api key
     *
     * @return void
     */
    public function delete_api_key() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            APIKey::find( $id )->delete();
        }

        $this->send_success();
    }

    /**
     * Dismiss promotional offer
     *
     * @since 1.1.15
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

        if ( ! empty( $_POST['erp_christmas_dismissed'] ) ) {
            $offer_key = 'erp_wedevs_19_blackfriday';
            update_option( $offer_key, 'hide' );
        }

        wp_die();
    }

}

new Ajax();
