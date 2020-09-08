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

        $this->process_data( $processed_data, $type );
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

    public function process_data( $process_data, $type ) {

        $error_list = [];
        foreach ( $process_data as $data_key => $data ) {
            if( 0 != $data_key ) {
                foreach ($data as $dt_key => $dt_value) {
                    $error_list[] = array(
                        'line_no'    => $data_key,
                        'field_type' => $dt_key,
                        'errors'     => $this->validate( $dt_key, $dt_value, $type )
                    );
                }
            }
        }
        echo '<pre>';
        print_r($error_list);
        die();
    }

    public function validate( $dt_key, $dt_value, $type ) {

        switch ( $dt_key ) {
            case "first_name":
                return $this->validate_field( "First name", $dt_value, $type, "not_empty:true|max:60|min:2|email:true|unique:first_name" );
                break;
            case "middle_name":
                return $this->validate_field( "Middle name", $dt_value, $type, "max:4|min:3" );
                break;
            case "last_name":
                return $this->validate_field( "Last name", $dt_value, $type, "max:4" );
                break;
            case "email":
                return $this->validate_field( "Email", $dt_value, $type, "max:60|unique" );
                break;
            case "employee_id":
                return $this->validate_field( "Employee id", $dt_value, $type, "max:20|unique" );
                break;
            case "phone":
                return $this->validate_field( "Phone", $dt_value, $type, "max:20|unique" );
                break;
            case "mobile":
                return $this->validate_field( "Mobile", $dt_value, $type, "max:20|unique" );
                break;
            case "other":
                return $this->validate_field( "Other", $dt_value, $type, "max:20|unique" );
                break;
            case "website":
                return $this->validate_field( "Webnsite", $dt_value, $type, "max:20|unique" );
                break;
            case "fax":
                return $this->validate_field( "Fax", $dt_value, $type, "max:20|unique" );
                break;
            case "notes":
                return $this->validate_field( "Notes", $dt_value, $type, "max:20|unique" );
                break;
            case "street_1":
                return $this->validate_field( "Street 1", $dt_value, $type, "max:20|unique" );
                break;
            case "street_2":
                return $this->validate_field( "Street 2", $dt_value, $type, "max:20|unique" );
                break;
            case "city":
                return $this->validate_field( "City", $dt_value, $type, "max:20|unique" );
                break;
            case "state":
                return $this->validate_field( "State", $dt_value, $type, "max:20|unique" );
                break;
            case "postal_code":
                return $this->validate_field( "Postal code", $dt_value, $type, "max:20|unique" );
                break;
            case "country":
                $this->validate_field( "Country", $dt_value, $type, "max:20|unique" );
                break;
            case "currency":
                return $this->validate_field( "Currency", $dt_value, $type, "max:20|unique" );
                break;
            case "user_email":
                return $this->validate_field( "User email", $dt_value, $type, "max:20|unique:user_email" );
                break;
            case "designation":
                $this->validate_field( "Designation", $dt_value, $type, "max:20|unique" );
                break;
            case "department":
                return $this->validate_field( "Department", $dt_value, $type, "max:20|unique" );
                break;
            case "location":
                return $this->validate_field( "Location", $dt_value, $type, "max:20|unique" );
                break;
            case "hiring_source":
                return $this->validate_field( "hiring source", $dt_value, $type, "max:20|unique" );
                break;
            case "hiring_date":
                return $this->validate_field( "Hiring date", $dt_value, $type, "max:20|unique" );
                break;
            case "date_of_birth":
                return $this->validate_field( "Date of birth", $dt_value, $type, "max:20|unique" );
                break;
            case "reporting_to":
                return $this->validate_field( "Reporting to", $dt_value, $type, "max:20|unique" );
                break;
            case "pay_rate":
                return $this->validate_field( "Pay rate", $dt_value, $type, "max:20|unique" );
                break;
            case "type":
                $this->validate_field( "Type", $dt_value, $type, "max:20|unique" );
                break;
            case "status":
                return $this->validate_field( "Statue", $dt_value, $type, "max:20|unique" );
                break;
            case "other_email":
                return $this->validate_field( "Other email", $dt_value, $type, "max:20|unique" );
                break;
            case "address":
                return $this->validate_field( "Address", $dt_value, $type, "max:20|unique" );
                break;
            case "work_phone":
                return $this->validate_field( "Work phone", $dt_value, $type, "max:20|unique" );
                break;
            case "gender":
                return $this->validate_field( "Gender", $dt_value, $type, "max:20|unique" );
                break;
            case "marital_status":
                return $this->validate_field( "Marital status", $dt_value, $type, "max:20|unique" );
                break;
            case "nationality":
                return $this->validate_field( "Nationality", $dt_value, $type, "max:20|unique" );
                break;
            case "driving_license":
                return $this->validate_field( "Driving licence", $dt_value, $type, "max:20|unique" );
                break;
            case "hobbies":
                return $this->validate_field( "Hobbies", $dt_value, $type, "max:20|unique" );
                break;
            case "user_url":
                return $this->validate_field( "User email", $dt_value, $type, "max:60|unique" );
                break;
            case "description":
                return $this->validate_field( "Description", $dt_value, $type, "max:20|unique" );
                break;
            default:
                return apply_filters( 'validate_field', $dt_key, $dt_value, $type );
        }
    }


    public function validate_field( $field_name, $field_value, $type, $rulesets ) {

        $errors = [];

        $rulesets = explode('|', $rulesets );

        $field_value = "tgutmann@moen.com";

        foreach ( $rulesets as $ruleset ) {
            $ruleset = explode(':', $ruleset );
            $rule_name  = $ruleset[0];
            $rule_value = $ruleset[1];

            switch ( $rule_name ) {

                case "not_empty":
                    if ( $rule_value == 'true' && empty( $field_value ) ) {
                        $errors[] = "{$field_name} can not be empty";
                    }
                    break;
                case "max":
                    if ( strlen( $field_value ) > $rule_value ) {
                        $errors[] = "{$field_name} can not be more than {$rule_value} charecters";
                    }
                    break;
                case "min":
                    if ( strlen( $field_value ) < $rule_value ) {
                        $errors[] = "{$field_name} can not be less than {$rule_value} charecters";
                    }
                    break;
                case "email":
                    if ( $rule_value == 'true' && ! is_email( $field_value ) ) {
                        $errors[] = "{$field_name} should be a valid email";
                    }
                    break;
                case "unique":
                    if ( $type == 'employee' ) {
                        $errors[] = $this->check_unique_employee( $rule_value, $field_value, $field_name );
                    }
                    if ( $type == 'contact' ) {
                        $errors[] = $this->check_unique_contact( $rule_value, $field_value, $field_name );
                    }
                    break;
                default:
                    //
            }
        }
        return $errors;
    }

    public function check_unique_contact( $column, $value, $field_name) {
        global $wpdb;

        $result = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}erp_peoples WHERE {$column}=%s",  $value ) );
        if ( $result > 0 ) {
            return "{$field_name} already exists. Try different one";
        }
    }

    public function check_unique_employee( $column, $value, $field_name) {
        global $wpdb;

        $result = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_employees as emp LEFT JOIN {$wpdb->prefix}users as users ON emp.user_id=users.id WHERE {$column}=%s",  $value ) );
        if ( $result > 0 ) {
            return "{$field_name} already exists. Try different one";
        }
    }
}


