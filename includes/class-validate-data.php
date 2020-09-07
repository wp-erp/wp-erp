<?php
namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;

class Validate_Data {

    use Hooker;

    private $errors = [];

    private $has_error = false;

    public function __construct() {
        $this->action( 'validate_csv_data', 'validate_csv_data', 10, 3 );
        $this->action( 'erp_tool_import_csv_action', 'pre_validate_csv_data' );
    }

    public function validate_csv_data( $csv_data, $fields, $type ) {

        $fields         = array_flip( $fields );
        $processed_data = [];

        foreach ( $csv_data as $csvd ) {
           if( ! empty( $csvd ) ) {
               $person_data = [];
               foreach ($fields as $field_key => $field_value) {
                   $person_data[$field_value] = $csvd[$field_key];
               }
               $processed_data[] = $person_data;
           }
        }

        $this->process_data( $processed_data );
    }

    public function pre_validate_csv_data( $data ) {

        // Check if current user has permission
        if ( ! current_user_can( 'erp_hr_manager' ) ) {
            return new \WP_Error( 'no-permission', __( 'Sorry ! You do not have permission to access this page', 'erp' ) );
        }

        $files = wp_check_filetype_and_ext( $data['file']['tmp_name'], $data['file']['name'] );

        // Check if current user has permission
        if ( 'csv' != $files['ext'] && 'text/csv' != $files['type'] ) {
            return new \WP_Error( 'no-permission', __( 'Sorry ! You have to provide valid csv file', 'erp' ) );
        }
    }

    public function process_data( $process_data ) {

        foreach ( $process_data as $data_key => $data ) {
            if( 0 != $data_key ) {
                foreach ($data as $dt_key => $dt_value) {
                    $this->validate($dt_key, $dt_value);
                }
            }
        }

        die();
    }

    public function validate( $dt_key, $dt_value ) {
        //echo "{$dt_key} {$dt_value} <br>";
        switch ( $dt_key ) {
            case "first_name":
                echo "Your favorite color is red!";
                break;
            case "last_name":
                echo "Your favorite color is blue!";
                break;
            case "email":
                echo "Your favorite color is green!";
                break;
            default:
                echo "Your favorite color is neither red, blue, nor green!";
        }
    }
}


