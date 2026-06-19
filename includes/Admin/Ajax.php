<?php

namespace WeDevs\ERP\Admin;

use WeDevs\ERP\Admin\Models\CompanyLocations;
use WeDevs\ERP\Company;
use WeDevs\ERP\Framework\Models\APIKey;
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
        $this->action( 'wp_ajax_erp-company-location', 'location_create' );
        $this->action( 'wp_ajax_erp-delete-comp-location', 'location_remove' );
        $this->action( 'wp_ajax_erp_audit_log_view', 'view_edit_log_changes' );
        $this->action( 'wp_ajax_erp_file_upload', 'file_uploader' );
        $this->action( 'wp_ajax_erp_file_del', 'file_delete' );
        $this->action( 'wp_ajax_erp_people_exists', 'check_people' );
        $this->action( 'wp_ajax_erp_imap_test_connection', 'imap_test_connection' );
        $this->action( 'wp_ajax_erp_check_gmail_connection_established', 'check_gmail_connection_established' );
        $this->action( 'wp_ajax_erp_import_users_as_contacts', 'import_users_as_contacts' );
        $this->action( 'wp_ajax_erp-api-key', 'new_api_key' );
        $this->action( 'wp_ajax_erp-api-delete-key', 'delete_api_key' );
        $this->action( 'wp_ajax_erp-dismiss-promotional-offer-notice', 'dismiss_promotional_offer' );
        $this->action( 'wp_ajax_erp-toggle-module', 'toggle_module' );
        $this->action( 'wp_ajax_erp_import_csv', 'import_csv' );
        $this->action( 'wp_ajax_erp_acct_get_sample_csv_url', 'generate_csv_url' );
        $this->action( 'wp_ajax_erp_reset_data', 'erp_reset_data' );
        $this->action( 'wp_ajax_erp_dismiss_offer', 'dismiss_offer' );
    }

    /**
     * Generates url for accounting people
     *
     * @since 1.8.5
     *
     * @return mixed
     */
    public function generate_csv_url() {
        $this->verify_nonce( 'erp-import-export-nonce' );

        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_ac_create_customer' ) && ! current_user_can( 'erp_ac_create_vendor' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $path = ! empty( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';

        switch ( $type ) {
            case 'customers':
                $type = 'customer';
                break;

            case 'vendors':
                $type = 'vendor';
                break;
        }

        $nonce = wp_create_nonce( 'erp-import-export-nonce' );
        $page  = "?page=erp-accounting&action=download_sample&type={$type}&_wpnonce={$nonce}#{$path}";
        $url   = admin_url( "admin.php{$page}" );

        $this->send_success( $url );
    }

    /**
     * Imports CSV file in ERP
     *
     * @since 1.8.5
     *
     * @return mixed
     */
    public function import_csv() {
        $this->verify_nonce( 'erp-import-export-nonce' );

        if ( ! is_user_logged_in() ) {
            $this->send_error( __( 'Sorry ! You do not have permission to access this page', 'erp' ) );
        }

        $capability_for_type = [
            'employee' => 'erp_create_employee',
            'contact'  => 'erp_crm_add_contact',
            'company'  => 'erp_crm_add_contact', //NB: no capability for company, using contact capability
            'customer' => 'erp_ac_create_customer',
            'vendor'   => 'erp_ac_create_vendor',
        ];

        $type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

        if ( ! in_array( $type, [ 'contact', 'company', 'employee', 'vendor', 'customer' ], true ) ) {
            $this->send_error( __( 'Unknown import type!', 'erp' ) );
        }

        if ( ! current_user_can( 'administrator' ) && ! current_user_can( $capability_for_type[ $type ] ) ) {
            $this->send_error( __( 'Sorry ! You do not have permission to access this page', 'erp' ) );
        }

        if ( empty( $_FILES['csv_file'] ) ) {
            $this->send_error( __( 'No CSV file selected!', 'erp' ) );
        }

        $file_name    = isset( $_FILES['csv_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['csv_file']['name'] ) ) : '';
        $file_tmpname = isset( $_FILES['csv_file']['tmp_name'] ) ? sanitize_url( wp_unslash( $_FILES['csv_file']['tmp_name'] ) ) : '';
        $file_info    = wp_check_filetype_and_ext( $file_tmpname, $file_name );

        if ( 'csv' !== $file_info['ext'] && 'text/csv' !== $file_info['type'] ) {
            $this->send_error( __( 'The file is not a valid CSV file! Please provide a valid one.', 'erp' ) );
        }

        $fields = ! empty( $_POST['fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fields'] ) ) : [];

        $csv = new \ParseCsv\Csv();
        $csv->auto( $file_tmpname );

        if ( empty( $csv->data ) ) {
            $this->send_error( __( 'No data found to import!', 'erp' ) );
        }

        $csv_data   = [];
        $csv_data[] = array_keys( $csv->data[0] );
        $count  = 0;

        foreach ( $csv->data as $data_item ) {
            $csv_data[] = array_values( $data_item );
        }

        if ( ! empty( $csv_data ) ) {
            $this->validate_csv_data( $csv_data, $fields, $type );
            $count =  $this->process_csv_data( $csv_data, $fields, $type, $count );
        }

        if ( 0 === $count ) {
            $this->send_error( __( 'Something went wrong or items already exist', 'erp' ) );
        }

        $this->send_success( sprintf( __( '%d items have been imported successfully', 'erp' ), $count ) );
    }

    /**
     * Validate CSV data
     *
     * @param array  $csv_data
     * @param array  $fields
     * @param string $type
     *
     * @return void
     */

    public function validate_csv_data( $csv_data, $fields, $type ) {
        $errors = [];

        $errors = apply_filters( 'erp_validate_csv_data', $csv_data, $fields, $type );

        if ( ! empty( $errors ) ) {
            $error_html = '<ul class="erp-list">';

            foreach ( $errors as $error ) {
                $error_html .= "<li>{$error}</li>";
            }

            $error_html = $error_html . '</ul>';

            $this->send_error( $error_html );
        }
    }

    /**
     * Process CSV data
     *
     * @param array  $csv_data
     * @param array  $fields
     * @param string $type
     * @param int    $count
     *
     * @return int
     */
    public function process_csv_data( $csv_data, $fields, $type, $count ) {
        unset( $csv_data[0] ); // remove the column head row
       $designations =  erp_hr_get_designations_fresh();
       $departments =  erp_hr_get_departments_fresh();
       $employee_fields = [
            'work'     => [
                'designation',
                'department',
                'location',
                'hiring_source',
                'hiring_date',
                'date_of_birth',
                'reporting_to',
                'pay_rate',
                'pay_type',
                'type',
                'status',
                'location',
            ],
            'personal' => [
                'employee_id',
                'photo_id',
                'user_id',
                'first_name',
                'middle_name',
                'last_name',
                'other_email',
                'phone',
                'work_phone',
                'mobile',
                'address',
                'gender',
                'marital_status',
                'nationality',
                'driving_license',
                'hobbies',
                'user_url',
                'description',
                'street_1',
                'street_2',
                'city',
                'country',
                'state',
                'postal_code',
            ],
        ];
        $reporting_to = []; // for mapping reporting to after import employee
        $GLOBALS['job_info'] = []; // for processing job history after import employee

        foreach ( $csv_data as $line ) {

            if ( empty( $line ) ) {
                continue;
            }

            $line_data = [];

            if ( is_array( $fields ) && ! empty( $fields ) ) {
                foreach ( $fields as $key => $value ) {

                    if ( ! empty( $line[ $value ] ) ) {
                        if ( $type === 'employee' ) {


                            if ( in_array( $key, $employee_fields['work'], true ) ) {
                                if ( $key === 'designation' && ! empty( $line[ $value ] ) ) {
                                   if( ! array_search( $line[ $value ], $designations, true ) ){

                                        $result = erp_hr_create_designation(['title' => $line[ $value ]]);

                                        if ( is_wp_error( $result ) ) {
                                            error_log( $result->get_error_message() );
                                        } else {
                                            $line_data['work'][ $key ] = $result;
                                            $designations[ $result ] = $line[ $value ]; // add the new item to existing designations list
                                        }

                                   }else{
                                        $line_data['work'][ $key ] = array_search( $line[ $value ], $designations, true );
                                   }
                                } elseif ( $key === 'department' && ! empty( $line[ $value ] ) ) {

                                    if( ! array_search( $line[ $value ], $departments, true ) ){

                                        $result = erp_hr_create_department(['title' => $line[ $value ]]);

                                        if ( is_wp_error( $result ) ) {
                                            error_log( $result->get_error_message() );
                                        } else {
                                            $line_data['work'][ $key ] = $result;
                                            $departments[ $result ] = $line[ $value ]; // add the new item to existing departments list
                                        }
                                    }else{
                                        $line_data['work'][ $key ] = array_search( $line[ $value ], $departments, true );
                                    }

                                }elseif ( $key === 'type' ) {
                                    $line_data['work'][ $key ] = array_search( $line[ $value ], erp_hr_get_employee_types(), true );
                                }elseif ( $key === 'pay_type' ) {
                                    $line_data['work'][ $key ] = array_search( $line[ $value ], erp_hr_get_pay_type(), true );
                                }elseif ( $key === 'hiring_source' ) {
                                    $line_data['work'][ $key ] = array_search( $line[ $value ], erp_hr_get_employee_sources(), true );
                                }elseif ( $key === 'status' ) {
                                    $line_data['work'][ $key ] = array_search( $line[ $value ], erp_hr_get_employee_statuses(), true );
                                }elseif ( $key === 'location' ) {
                                    $locations = erp_company_get_location_dropdown_raw();

                                    if ( ! array_search( $line[ $value ], $locations ) ) {
                                        $line_data['work'][ $key ] = '-1'; // default location
                                    } else {
                                        $line_data['work'][ $key ] = array_search( $line[ $value ], $locations, true );
                                    }
                                }elseif( $key === 'reporting_to' && $line[ $value ] !== '' ) {
                                    $line_data['work'][ $key ] = $line[ $value ];
                                } else {
                                    $line_data['work'][ $key ] = $line[ $value ];
                                }
                            } elseif ( in_array( $key, $employee_fields['personal'], true ) ) {
                                if ( $key === 'gender' ) {
                                    $line_data['personal'][ $key ] = array_search( $line[ $value ], erp_hr_get_genders(), true );
                                }elseif ( $key === 'marital_status' ) {
                                    $line_data['personal'][ $key ] = array_search( $line[ $value ], erp_hr_get_marital_statuses(), true );
                                }else {
                                    $line_data['personal'][ $key ] = $line[ $value ];
                                }
                            } else {
                                $line_data[ $key ] = $line[ $value ];
                            }
                        } else {
                            $line_data[ $key ] = isset( $line[ $value ] ) ? $line[ $value ] : '';
                            $line_data['type'] = $type;
                        }
                    }
                }
            }

            if ( 'employee' === $type ) {
                if ( ! isset( $line_data['work']['status'] ) ) {
                    $line_data['work']['status'] = 'active';
                }

                $item_insert_id = erp_hr_employee_create( $line_data );

                if ( ! is_wp_error( $item_insert_id ) && ! empty( $line_data['work']['reporting_to'] ) ) {
                    /**
                     * Add reporting to array for processing job history after import employee
                     */
                    $reporting_to[] = [
                        'user_id'      => $item_insert_id,
                        'reporting_to' => $line_data['work']['reporting_to'],
                    ];

                    $GLOBALS['job_info'][$item_insert_id]['user_id'] = $item_insert_id;
                }

                if ( is_wp_error( $item_insert_id ) || is_string( $item_insert_id ) ) {
                    continue;
                }
            } else if ( 'vendor' === $type || 'customer' === $type ) {
                $item_insert_id = erp_insert_people( $line_data );

                if ( is_wp_error( $item_insert_id ) ) {
                    continue;
                }
            } else if ( 'contact' === $type || 'company' === $type ) {
                $contact_owner              = ! empty( $_POST['contact_owner'] )
                                              ? absint( wp_unslash( $_POST['contact_owner'] ) )
                                              : erp_crm_get_default_contact_owner();

                if ( 'contact' === $type && ( ! erp_crm_is_current_user_manager() ) && erp_crm_is_current_user_crm_agent() && $contact_owner !== get_current_user_id() ) {
                    $this->send_error( __( 'You can only import your own contacts', 'erp' ) );
                }

                $line_data['contact_owner'] = $contact_owner;
                $people                     = erp_insert_people( $line_data, true );

                if ( is_wp_error( $people ) ) {
                    continue;
                }

                $contact       = new \WeDevs\ERP\CRM\Contact( absint( $people->id ), 'contact' );
                $life_stage    = isset( $_POST['life_stage'] ) ? sanitize_key( $_POST['life_stage'] ) : '';

                if ( ! $people->exists ) {
                    $contact->update_life_stage( $life_stage );
                } else if ( ! $contact->get_life_stage() ) {
                    $contact->update_life_stage( $life_stage );
                }

                if ( ! empty( $_POST['contact_group'] ) ) {
                    $contact_group = absint( $_POST['contact_group'] );

                    $existing_data = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( [
                        'group_id' => $contact_group,
                        'user_id'  => $people->id,
                    ] )->first();

                    if ( empty( $existing_data ) ) {
                        $hash = sha1( microtime() . 'erp-subscription-form' . $contact_group . $people->id );

                        erp_crm_create_new_contact_subscriber( [
                            'group_id'       => $contact_group,
                            'user_id'        => $people->id,
                            'status'         => 'subscribe',
                            'subscribe_at'   => current_time( 'mysql' ),
                            'unsubscribe_at' => null,
                            'hash'           => $hash,
                        ] );
                    }
                }
            }

            ++ $count;
        }
        $this->map_reporting_to_employee( $reporting_to );
        $this->update_reporting_history( $GLOBALS['job_info'] );
        return $count;
    }

    /**
     * Map reporting to employee by email
     *
     * @param array $reporting_to
     *
     * @return void
     */
    function map_reporting_to_employee( $reporting_to ) {
        global $wpdb;

        foreach ( $reporting_to as $value ) {
            if ( ! empty( $value['reporting_to'] ) && ! empty( $value['user_id'] ) ) {
                $reporting_employee = get_user_by( 'email', $value['reporting_to'] );

                if ( $reporting_employee ) {
                    $reporting_employee_id = $reporting_employee->ID;


                     // Update reporting to employee id in erp_hr_employees table
                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}erp_hr_employees SET reporting_to = %d WHERE user_id = %d",
                            $reporting_employee_id,
                            $value['user_id']
                        )
                    );
                    /**
                     * Add reporting to employee id to job history array for processing job history after import employee
                     */
                    $GLOBALS['job_info'][$value['user_id']][ 'reporting_to'] = $reporting_employee_id;
                }
            }
        }
    }

    /**
     * Update reporting history by history id
     *
     * @param array $job_history
     *
     * @return void
     */
    function update_reporting_history($job_history) {
        global $wpdb;
        foreach ( $job_history as $value ) {
            if ( ! empty( $value["id"] ) ) {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}erp_hr_employee_history SET data = %d WHERE id = %d",
                        $value['reporting_to'],
                        $value["id"]

                    )
                );
            }
        }
        unset( $GLOBALS['job_info']  );
    }

    /**
     * Deletes a file
     *
     * @return mixed
     */
    public function file_delete() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

        $attach_id   = isset( $_POST['attach_id'] ) ? sanitize_text_field( wp_unslash( $_POST['attach_id'] ) ) : 0;
        $custom_attr = isset( $_POST['custom_attr'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['custom_attr'] ) ) : [];
        $upload      = new \WeDevs\ERP\Uploader();

        if ( is_array( $attach_id ) ) {
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
    public function file_uploader() {
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
        $zip           = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '';
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
            'country'    => $country,
        ];

        $company     = new Company();
        $location_id = $company->create_location( $args );

        if ( is_wp_error( $location_id ) ) {
            $this->send_error( $location_id->get_error_message() );
        }

        $this->send_success( [ 'id' => $location_id, 'title' => $location_name ] );
    }

    /**
     * Remove a location
     *
     * @return void
     */
    public function location_remove() {

        // check permission for deleting location
        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_permission'] ) );
        }

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            return;
        }

        $location_id   = isset( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : 0;

        if ( $location_id ) {
            CompanyLocations::find( $location_id )->delete();
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

        $log       = \WeDevs\ERP\Admin\Models\AuditLog::find( $log_id );
        $old_value = maybe_unserialize( base64_decode( $log->old_value ) );
        $new_value = maybe_unserialize( base64_decode( $log->new_value ) );
        ob_start(); ?>
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
                    <?php foreach ( $old_value as $key => $value ) {
                         if(is_array($value) && is_array($new_value[$key]) ) {
                            foreach ($value as $sub_key => $sub_value) {
                                if ( isset( $new_value[$key][$sub_key] ) ) {
                                    ?>
                                    <tr class="<?php echo $i % 2 == 0 ? 'alternate' : 'odd'; ?>">
                                        <td class="col-date"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) . ' - ' . ucfirst( str_replace( '_', ' ', $sub_key ) ) ); ?></td>
                                        <td><?php echo ( $sub_value ) ? esc_html( wp_unslash( $sub_value ) ) : '--'; ?></td>
                                        <td><?php echo ( isset( $new_value[$key][$sub_key] ) && $new_value[$key][$sub_key] ) ? esc_html( wp_unslash( $new_value[$key][$sub_key] ) ) : '--'; ?></td>
                                    </tr>
                                    <?php
                                } else {
                                    ?>
                                    <tr class="<?php echo $i % 2 == 0 ? 'alternate' : 'odd'; ?>">
                                        <td class="col-date"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) . ' - ' . ucfirst( str_replace( '_', ' ', $sub_key ) ) ); ?></td>
                                        <td><?php echo ( $sub_value ) ? esc_html( wp_unslash( $sub_value ) ) : '--'; ?></td>
                                        <td><?php echo '--'; ?></td>
                                    </tr>
                                    <?php
                                }
                            }

                         }else{


                        ?>

                        <tr class="<?php echo $i % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td class="col-date"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?></td>
                            <td><?php echo ( $value ) ? esc_html( wp_unslash( $value ) ) : '--'; ?></td>
                            <td><?php echo ( $new_value[$key] ) ? esc_html( wp_unslash( $new_value[$key] ) ) : '--'; ?></td>
                        </tr>
                        <?php } ?>
                    <?php $i++; } ?>
                </tbody>
            </table>
        </div>
        <?php
        $content = ob_get_clean();

        $data = [
            'title'   => esc_html__( 'Log changes', 'erp' ),
            'content' => $content,
        ];

        $this->send_success( $data );
    }

    /**
     * Check if a people exists
     *
     * @return void
     */
    public function check_people() {
        // Verify nonce for CSRF protection
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        // Check if user has permission to access contact data
        if ( ! current_user_can( 'erp_crm_list_contact' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $email = isset( $_REQUEST['email'] ) ? sanitize_email( wp_unslash( $_REQUEST['email'] ) ) : false;

        if ( ! $email ) {
            $this->send_error( esc_html__( 'No email address provided', 'erp' ) );
        }

        $user = \get_user_by( 'email', $email );

        if ( false === $user ) {
            $people = erp_get_people_by( 'email', $email );
        } else {
            $peep = \WeDevs\ERP\Framework\Models\People::with( 'types' )->whereUserId( $user->ID )->first();

            if ( null === $peep ) {
                // Create a simple object for WP users not in ERP
                $people = new \stdClass();
                $people->id = null; // No ERP ID for WP-only users
                $people->types = array( 'wp_user' );
            } else {
                $people = (object) $peep->toArray();
                $people->types = wp_list_pluck( $peep->types->toArray(), 'name' );
            }
        }

        // we didn't found any user with this email address
        if ( !$people ) {
            $this->send_error();
        }

        // Return only essential, non-sensitive information
        $safe_data = array(
            'exists' => true,
            'id' => isset( $people->id ) ? $people->id : null,
            'types' => isset( $people->types ) ? $people->types : array()
        );

        // seems like we found one
        $this->send_success( $safe_data );
    }

    /**
     * Test the Imap connection.
     *
     * @return void
     */
    public function imap_test_connection() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_permission'] ) );
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

            if ( $imap->is_connected() ) {
                $option = get_option( 'erp_settings_erp-email_imap', [] );
                $option['imap_status'] = 1;
                update_option( 'erp_settings_erp-email_imap', $option );

                $this->send_success( esc_html__( 'Your IMAP connection is established.', 'erp' ) );
            }
        } catch ( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Check if GMAIL/G-Suit connection is established based on connection-data
     *
     * @return void
     */
    public function check_gmail_connection_established() {
        $this->verify_nonce( 'erp-settings-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_permission'] ) );
        }

        if ( wperp()->google_auth->has_credentials() ) {
            $url = wperp()->google_auth->get_client()->createAuthUrl();

            if ( is_wp_error( $url ) ) {
                $this->send_error( erp_get_message( ['type' => 'error_process'] ) );
            }

            $data = [
                'link'           => $url,
                'status'         => true,
                'is_connected'   => (boolean) wperp()->google_auth->is_connected(),
                'disconnect_url' => wperp()->google_auth->get_disconnect_url()
            ];

            $this->send_success( $data );
        }

        $this->send_error( __('No credential set yet !', 'erp') );
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

        if ( ! erp_crm_is_current_user_manager() && erp_crm_is_current_user_crm_agent() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        define( 'ERP_IS_IMPORTING', true );

        $limit   = 50; // Limit to import per request
        $attempt = get_option( 'erp_users_to_contacts_import_attempt', 1 );

        update_option( 'erp_users_to_contacts_import_attempt', $attempt + 1 );

        $offset        = ( $attempt - 1 ) * $limit;

        $user_role     = isset( $_REQUEST['user_role'] )     ? sanitize_text_field( wp_unslash( $_REQUEST['user_role'] ) )     : '';
        $contact_owner = isset( $_REQUEST['contact_owner'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contact_owner'] ) ) : '';
        $life_stage    = isset( $_REQUEST['life_stage'] )    ? sanitize_text_field( wp_unslash( $_REQUEST['life_stage'] ) )    : '';
        $contact_group = isset( $_REQUEST['contact_group'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contact_group'] ) ) : '';

        $query_args = [
            'number'   => $limit,
            'offset'   => $offset
        ];

        if ( ! empty( $user_role ) ) {
            $query_args['role__in'] = $user_role;
        }

        $user_query  = new \WP_User_Query( $query_args );
        $users       = $user_query->get_results();
        $total_items = $user_query->get_total();

        $user_ids    = [];
        $user_ids    = wp_list_pluck( $users, 'ID' );

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
                'life_stage'    => $life_stage,
            ];

            $people = erp_insert_people( $data, true );

            if ( is_wp_error( $people ) ) {
                continue;
            } else {
                $contact = new \WeDevs\ERP\CRM\Contact( absint( $people->id ), 'contact' );

                if ( ! $people->exists ) {
                    $contact->update_life_stage( $life_stage );
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

                    erp_crm_create_new_contact_subscriber( [
                        'group_id'          => $contact_group,
                        'user_id'           => $people->id,
                        'status'            => 'subscribe',
                        'subscribe_at'      => current_time( 'mysql' ),
                        'unsubscribe_at'    => null,
                        'hash'              => $hash,
                    ] );
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
                'user_id' => isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0,
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

    /**
     * Toggle Modules
     *
     * @since 1.8.2
     *
     * @return void
     */
    public function toggle_module() {
        $this->verify_nonce( 'wp-erp-toggle-module' );

        // Check permission
        if ( current_user_can( 'manage_options' ) === false ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! empty( $_POST['module_id'] ) ) {
            if ( is_array( $_POST['module_id'] ) ) {
                $module_ids   = array_map( 'sanitize_text_field', wp_unslash( $_POST['module_id'] ) );
            } else {
                $module_ids[] = sanitize_text_field( wp_unslash( $_POST['module_id'] ) );
            }
        }

        // check for valid module
        if ( true !== ( $ret = wperp()->modules->is_valid_module( $module_ids ) ) ) {
            $this->send_error( $ret->get_error_message() );
        }

        $toggle = isset( $_POST['toggle'] ) ? sanitize_text_field( wp_unslash( $_POST['toggle'] ) ) : '';

        if ( ! empty( $toggle ) && $toggle != -1 && ! empty( $module_ids ) ) {

            if ( 'activate' === $toggle ) {
                // activate modules
                wperp()->modules->activate_modules( $module_ids );
            }
            elseif ( 'deactivate' === $toggle ) {
                // de-activate modules
                wperp()->modules->deactivate_modules( $module_ids );
            }

            $this->send_success( esc_html__( 'Redirecting...', 'erp' ) );

        } else {
            $this->send_error( __( 'Invalid input.', 'erp') );
        }

    }

    /**
     * Reset WP ERP Data
     *
     * @since 1.8.8
     *
     * @return void
     */
    public function erp_reset_data() {

        $this->verify_nonce( 'erp-reset-nonce' );

        if ( current_user_can( 'manage_options' ) === false ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $reset_text = sanitize_text_field( wp_unslash( $_POST['erp_reset_confirmation'] ) );

        if ( ! isset( $_POST['erp_reset_confirmation'] ) || 'Reset' !== sanitize_text_field( wp_unslash( $_POST['erp_reset_confirmation'] ) ) ) {
            $this->send_error( esc_html__( 'Invalid confirmation text. Please give valid confirmation text.', 'erp' ) );
        }

        $resetted = erp_reset_data();

        if ( is_wp_error( $resetted ) ) {
            $this->send_error( esc_html__( 'Sorry, Something went wrong. Please try again !', 'erp' ) );
        }

        $this->send_success(
            [
                'message'        => esc_html__( 'Resetted WP ERP successfully. You will be redirected soon. Please Setup WP ERP again or Skip to continue.', 'erp' ),
                'redirected_url' => admin_url( "admin.php?page=erp-setup" ),
            ]
        );
    }

    /**
     * Dismiss promotion notice
     *
     * @since 1.16.2
     *
     * @return void
     */
    public function dismiss_offer() {

        if ( empty( $_POST['nonce'] ) && ! isset( $_POST['wperp_offer_key'] ) ) {

            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wperp-dismiss-offer-notice' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'erp' ) );
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'You have no permission to do that', 'erp' ) );
            return;
        }

        $offer_key    = 'wperp_offer_notice';
        $disabled_key = sanitize_text_field( wp_unslash( $_POST['wperp_offer_key'] ) );

        update_option( $offer_key, $disabled_key );
    }
}
