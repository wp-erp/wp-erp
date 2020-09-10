<?php
namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\ERP_Errors as ERP_Errors;
use WP_Error;

class Validate_Data {

    use Hooker;

    /**
     * Construct required validation
     *
     * @since 1.6.5
     *
     * @return void
     */

    public function __construct() {
        $this->action( 'erp_tool_import_csv_action', 'pre_validate_csv_data' );
        $this->action( 'validate_csv_data', 'validate_csv_data', 10, 3 );
    }

    /**
     * Pre validate csv data
     *
     * @since 1.6.5
     *
     * @return void
     */
    public function pre_validate_csv_data( $data ) {

        $errors = new ERP_Errors( 'import_csv_data' );
        // Check if current user has permission
        if ( ! current_user_can( 'erp_hr_manager' ) ) {
            $errors->add( new \WP_Error( 'no-permission', __( 'Sorry ! You do not have permission to access this page', 'erp' ) ) );
        }

        $files = wp_check_filetype_and_ext( $data['file']['tmp_name'], $data['file']['name'] );

        // Check if current user has permission
        if ( 'csv' != $files['ext'] && 'text/csv' != $files['type'] ) {
            $errors->add( new \WP_Error( 'no-permission', __( 'Sorry ! You have to provide valid csv file', 'erp' ) ) );
        }
        $this->through_error_if_found( $errors );
    }

    /**
     * Validate csv data
     *
     * @since 1.6.5
     *
     * @return void
     */
    public function validate_csv_data( $csv_data, $fields, $type ) {

        $prodessed_data = $this->filter_validate_csv_data( $csv_data, $fields, $type );

        $this->process_errors( $prodessed_data );
    }

    /**
     * Process errors
     *
     * @since 1.6.5
     *
     * @return void
     */
    public function process_errors( $prodessed_data ) {

        $errors = new ERP_Errors( 'import_csv_data' );

        foreach ( $prodessed_data as $pdata ) {
            if ( isset( $pdata['errors'] ) && ! empty( $pdata['errors'] ) ) {
                foreach ( $pdata['errors'] as $err ) {
                    $errors->add( new WP_Error( 'csv_error_' . $pdata['field_name'], esc_attr__( $err . " at line no " . $pdata['line_no'], 'erp' ) ) );
                }
            }
        }
        $this->through_error_if_found( $errors );
    }

    /**
     * Through errors if found & redirect
     *
     * @since 1.6.5
     *
     * @return void
     */
    public function through_error_if_found( $errors ) {
        if ( $errors->has_error() ) {
            $errors->save();
            $redirect_to = add_query_arg( array( 'error' => $errors->get_key() ), admin_url( "admin.php?page=erp-tools&tab=import" ) );
            wp_safe_redirect( $redirect_to );
            exit;
        }
    }

    /**
     * Filter & validate csv data, field & type
     *
     * @since 1.6.5
     *
     * @return array
     */
    public function filter_validate_csv_data( $csv_data, $fields, $type ) {
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

        $prodessed_data = $this->process_data( $processed_data, $type );

        return $prodessed_data;
    }

    /**
     * Process collected csv data for validatiob
     *
     * @since 1.6.5
     *
     * @return array
     */
    public function process_data( $process_data, $type ) {

        $error_list = [];
        foreach ( $process_data as $data_key => $data ) {
            if( 0 != $data_key ) {
                foreach ($data as $dt_key => $dt_value) {
                    $error_list[] = array(
                        'line_no'     => $data_key,
                        'field_name'  => $dt_key,
                        'field_value' => $dt_value,
                        'errors'      => $this->validate( $dt_key, $dt_value, $type )
                    );
                }
            }
        }
        return $error_list;
    }

    /**
     * Validate csv data by field
     *
     * @since 1.6.5
     *
     * @return array
     */
    public function validate( $dt_key, $dt_value, $type ) {

        switch ( $dt_key ) {
            case "first_name":
                return $this->validate_field( "First name", $dt_value, $type, "not_empty:true|max:60|min:2" );
                break;
            case "middle_name":
                return $this->validate_field( "Middle name", $dt_value, $type, "max:60|min:2" );
                break;
            case "last_name":
                return $this->validate_field( "Last name", $dt_value, $type, "not_empty:true|max:60|min:2" );
                break;
            case "email":
                return $this->validate_field( "Email", $dt_value, $type, "not_empty:true|max:90|min:2|email:true|unique:email" );
                break;
            case "employee_id":
                return $this->validate_field( "Employee id", $dt_value, $type, "max:20|min:3|unique:employee_id" );
                break;
            case "phone":
                return $this->validate_field( "Phone", $dt_value, $type, "max:20|min:3|is_phone:true" );
                break;
            case "mobile":
                return $this->validate_field( "Mobile", $dt_value, $type, "max:20|min:3|is_phone:true" );
                break;
            case "other":
                return $this->validate_field( "Other", $dt_value, $type, "max:50|min:3" );
                break;
            case "website":
                return $this->validate_field( "Webnsite", $dt_value, $type, "max:90|min:3" );
                break;
            case "fax":
                return $this->validate_field( "Fax", $dt_value, $type, "max:20|min:3" );
                break;
            case "notes":
                return $this->validate_field( "Notes", $dt_value, $type, "max:250|" );
                break;
            case "street_1":
                return $this->validate_field( "Street 1", $dt_value, $type, "max:250|" );
                break;
            case "street_2":
                return $this->validate_field( "Street 2", $dt_value, $type, "max:250|" );
                break;
            case "city":
                return $this->validate_field( "City", $dt_value, $type, "max:80|" );
                break;
            case "state":
                return $this->validate_field( "State", $dt_value, $type, "max:50|" );
                break;
            case "postal_code":
                return $this->validate_field( "Postal code", $dt_value, $type, "max:10|" );
                break;
            case "country":
                $this->validate_field( "Country", $dt_value, $type, "max:20|" );
                break;
            case "currency":
                return $this->validate_field( "Currency", $dt_value, $type, "max:5|" );
                break;
            case "life_stage":
                return $this->validate_field( "Currency", $dt_value, $type, "max:100|" );
                break;
            case "user_email":
                return $this->validate_field( "User email", $dt_value, $type, "not_empty:true|max:100|email:true|unique:user_email" );
                break;
            case "designation":
                $this->validate_field( "Designation", $dt_value, $type, "max:30|" );
                break;
            case "department":
                return $this->validate_field( "Department", $dt_value, $type, "max:30|" );
                break;
            case "location":
                return $this->validate_field( "Location", $dt_value, $type, "max:20|" );
                break;
            case "hiring_source":
                return $this->validate_field( "hiring source", $dt_value, $type, "max:20|" );
                break;
            case "hiring_date":
                return $this->validate_field( "Hiring date", $dt_value, $type, "max:20|is_date:true" );
                break;
            case "date_of_birth":
                return $this->validate_field( "Date of birth", $dt_value, $type, "max:20|is_date:true" );
                break;
            case "reporting_to":
                return $this->validate_field( "Reporting to", $dt_value, $type, "max:20|" );
                break;
            case "pay_rate":
                return $this->validate_field( "Pay rate", $dt_value, $type, "max:11|" );
                break;
            case "type":
                $this->validate_field( "Type", $dt_value, $type, "max:20|" );
                break;
            case "pay_type":
                $this->validate_field( "Type", $dt_value, $type, "max:20|" );
                break;
            case "status":
                return $this->validate_field( "Statue", $dt_value, $type, "max:10|" );
                break;
            case "other_email":
                return $this->validate_field( "Other email", $dt_value, $type, "max:60|email:true" );
                break;
            case "address":
                return $this->validate_field( "Address", $dt_value, $type, "max:200|" );
                break;
            case "work_phone":
                return $this->validate_field( "Work phone", $dt_value, $type, "max:20|is_phone:true" );
                break;
            case "gender":
                return $this->validate_field( "Gender", $dt_value, $type, "max:10|" );
                break;
            case "marital_status":
                return $this->validate_field( "Marital status", $dt_value, $type, "max:20|" );
                break;
            case "nationality":
                return $this->validate_field( "Nationality", $dt_value, $type, "max:30|" );
                break;
            case "driving_license":
                return $this->validate_field( "Driving licence", $dt_value, $type, "max:30|" );
                break;
            case "hobbies":
                return $this->validate_field( "Hobbies", $dt_value, $type, "max:200|" );
                break;
            case "user_url":
                return $this->validate_field( "User email", $dt_value, $type, "max:600|" );
                break;
            case "description":
                return $this->validate_field( "Description", $dt_value, $type, "max:200|" );
                break;
            default:
                return apply_filters( 'validate_field', $dt_key, $dt_value, $type );
        }
    }

    /**
     * Validate individual field data
     *
     * @since 1.6.5
     *
     * @return array
     */
    public function validate_field( $field_name, $field_value, $type, $rulesets ) {

        $errors = [];

        $rulesets = explode('|', $rulesets );

        foreach ( $rulesets as $ruleset ) {
            $ruleset = explode(':', $ruleset );
            $rule_name  = $ruleset[0];
            $rule_value = $ruleset[1];

            switch ( $rule_name ) {

                case "not_empty":
                    if ( $rule_value == 'true' && empty( $field_value ) ) {
                        $errors[] =  __( "{$field_name} can not be empty", "erp" );
                    }
                    break;
                case "max":
                    if ( strlen( $field_value ) > $rule_value ) {
                        $errors[] =  __( "{$field_name} can not be more than {$rule_value} charecters", "erp" );
                    }
                    break;
                case "min":
                    if ( strlen( $field_value ) < $rule_value ) {
                        $errors[] = __( "{$field_name} can not be less than {$rule_value} charecters", "erp" );
                    }
                    break;
                case "email":
                    if ( $rule_value == 'true' && ! is_email( $field_value ) ) {
                        $errors[] = __( "{$field_name} should be a valid email", "erp" );
                    }
                    break;
                case "is_date":
                    if ( $rule_value == 'true' ) {
                        $check_is_date = $this->is_valid_date( $rule_value, $field_value, $field_name );
                        if ( $check_is_date ) {
                            $errors[] = $check_is_date;
                        }
                    }
                    break;
                case "is_phone":
                    if ( $rule_value == 'true' ) {
                        $check_is_phone = $this->is_valid_phone( $rule_value, $field_value, $field_name );
                        if ( $check_is_phone ) {
                            $errors[] = $check_is_phone;
                        }
                    }
                    break;
                case "unique":
                    if ( $type == 'employee' ) {
                        $check_is_unique_emp = $this->check_unique_employee( $rule_value, $field_value, $field_name );
                        if ( $check_is_unique_emp ) {
                            $errors[] = $check_is_unique_emp;
                        }
                    }
                    if ( $type == 'contact' || $type == 'company' ) {
                        $check_is_unique_cont = $this->check_unique_contact( $rule_value, $field_value, $field_name );
                        if ( $check_is_unique_cont ) {
                            $errors[] = $check_is_unique_cont;
                        }
                    }
                    break;
                default:
                    $custom_error_check = apply_filters( 'custom_validation', $rule_value, $field_value, $field_name, $type );
                    if ( $custom_error_check ) {
                        $errors[] = $custom_error_check;
                    }
            }
        }
        return $errors;
    }

    /**
     * Check contact OR company specific field is unique or not
     *
     * @since 1.6.5
     *
     * @return string
     */
    public function check_unique_contact( $column, $value, $field_name) {
        global $wpdb;

        $result = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}erp_peoples WHERE {$column}=%s",  $value ) );
        if ( $result > 0 ) {
            return "{$field_name} already exists. Try different one";
        }
    }

    /**
     * Check employee specific field is unique or not
     *
     * @since 1.6.5
     *
     * @return string
     */
    public function check_unique_employee( $column, $value, $field_name) {
        global $wpdb;

        $result = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_employees as emp LEFT JOIN {$wpdb->prefix}users as users ON emp.user_id=users.id WHERE {$column}=%s",  $value ) );
        if ( $result > 0 ) {
            return __( "{$field_name} already exists. Try different one", "erp" );
        }
    }

    /**
     * Check date is valid or not
     *
     * @since 1.6.5
     *
     * @return string
     */
    public function is_valid_date( $column, $value, $field_name ) {
        if ( ! preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $value ) ) {
            return __( "{$field_name} should be a valid date. Ex: YYYY-MM-DD", "erp" );
        }
    }

    /**
     * Check phone is valid or not
     *
     * @since 1.6.5
     *
     * @return string
     */
    public function is_valid_phone( $column, $value, $field_name ) {
        if ( ! preg_match( "/\+[0-9]{2}+[0-9]{4}/s", $value ) ) {
            return __( "{$field_name} should be a valid phone/mobile no.", "erp" );
        }
    }
}


