<?php
namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\ErpErrors;
use WP_Error;

class ValidateData {

    use Hooker;

    private $csv_data = [];

    /**
     * Construct required validation
     *
     * @since 1.6.5
     *
     * @return void
     */
    public function __construct() {
        $this->filter( 'erp_validate_csv_data', 'validate_csv_data', 10, 3 );
        $this->filter( 'validate_field', 'validate_custom_field', 10, 3 );
    }

    /**
     * Validate csv data
     *
     * @since 1.6.5
     *
     * @return array
     */
    public function validate_csv_data( $csv_data, $fields, $type ) {
        $errors         = [];
        $processed_data = $this->filter_validate_csv_data( $csv_data, $fields, $type );
        $total_rows     = count( $processed_data );

        if ( ! empty( $processed_data ) ) {
            foreach ( $processed_data as $pdata_key => $pdata_val ) {
                $pdata_key_arr = explode( '_', $pdata_key );

                $errors[] = '<strong>' . sprintf( __( "Error at #ROW %d", 'erp' ), $pdata_key_arr[1] + 1 ) . '</strong>';

                foreach ( $pdata_val as $pdval ) {
                    foreach ( $pdval['errors'] as $err ) {
                        $errors[] = __( sprintf( "%s", $err ), 'erp' );
                    }
                }
            }
        }

        return $errors;
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
            if ( ! empty( $csvd ) ) {
                $person_data = [];
                foreach ( $fields as $field_key => $field_value ) {
                    $person_data[ $field_value ] = $csvd[ $field_key ];
                }
                $processed_data[] = $person_data;
            }
        }

        $this->csv_data = $processed_data;

        $prodessed_data = $this->process_data( $processed_data, $type );

        return $prodessed_data;
    }

    /**
     * Process collected csv data for validation
     *
     * @since 1.6.5
     *
     * @return array
     */
    public function process_data( $process_data, $type ) {
        $error_list = [];

        foreach ( $process_data as $data_key => $data ) {
            if ( 0 !== $data_key ) {
                foreach ( $data as $dt_key => $dt_value ) {
                    $cur_errors = $this->validate( $dt_key, $dt_value, $type );
                    if ( ! empty( $cur_errors ) ) {
                        $error_list[ "row_{$data_key}" ][] = array(
                            'line_no'     => $data_key,
                            'field_name'  => $dt_key,
                            'field_value' => $dt_value,
                            'errors'      => $cur_errors,
                        );
                    }
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
            case 'first_name':
                return $this->validate_field( 'First name', $dt_value, $type, 'not_empty:true|max:60|min:2|is_valid_name:true' );

            case 'middle_name':
                return $this->validate_field( 'Middle name', $dt_value, $type, 'max:60|is_valid_name:true' );

            case 'last_name':
                return $this->validate_field( 'Last name', $dt_value, $type, 'not_empty:true|max:60|min:2|is_valid_name:true' );

            case 'name':
                return $this->validate_field( 'Name', $dt_value, $type, 'not_empty:true|max:60|min:2|is_valid_name:true|unique:name' );

            case 'email':
                return $this->validate_field( 'Email', $dt_value, $type, 'not_empty:true|max:90|min:2|email:true|unique:email|not_csv_column_duplicate:email' );

            case 'employee_id':
                return $this->validate_field( 'Employee ID', $dt_value, $type, 'max:20|unique:employee_id|is_valid_emp_id:true' );

            case 'phone':
                return $this->validate_field( 'Phone', $dt_value, $type, 'min:4|max:22|is_phone:true' );

            case 'mobile':
                return $this->validate_field( 'Mobile', $dt_value, $type, 'min:4|max:22|is_phone:true' );

            case 'other':
                return $this->validate_field( 'Other', $dt_value, $type, 'max:50|' );

            case 'website':
                return $this->validate_field( 'Website', $dt_value, $type, 'max:90|is_valid_url:true' );

            case 'fax':
                return $this->validate_field( 'Fax', $dt_value, $type, 'max:20|is_phone:true' );

            case 'notes':
                return $this->validate_field( 'Notes', $dt_value, $type, 'max:250|' );

            case 'street_1':
                return $this->validate_field( 'Street 1', $dt_value, $type, 'max:250|' );

            case 'street_2':
                return $this->validate_field( 'Street 2', $dt_value, $type, 'max:250|' );

            case 'city':
                return $this->validate_field( 'City', $dt_value, $type, 'max:80|is_valid_name:true' );

            case 'state':
                return $this->validate_field( 'State', $dt_value, $type, 'max:50|' );

            case 'postal_code':
                return $this->validate_field( 'Postal code', $dt_value, $type, 'max:10|zip:true' );

            case 'country':
                return $this->validate_field( 'Country', $dt_value, $type, 'max:20|' );

            case 'currency':
                return $this->validate_field( 'Currency', $dt_value, $type, 'max:5|' );

            case 'life_stage':
                return $this->validate_field( 'Life stage', $dt_value, $type, 'max:100|' );

            case 'user_email':
                return $this->validate_field( 'User email', $dt_value, $type, 'not_empty:true|max:100|email:true|unique:user_email|not_csv_column_duplicate:user_email' );

            case 'designation':
                return $this->validate_field( 'Designation', $dt_value, $type, 'max:30|' );

            case 'department':
                return $this->validate_field( 'Department', $dt_value, $type, 'max:30|' );

            case 'location':
                return $this->validate_field( 'Location', $dt_value, $type, 'max:20|' );

            case 'hiring_source':
                return $this->validate_field( 'hiring source', $dt_value, $type, 'max:20|' );

            case 'hiring_date':
                return $this->validate_field( 'Hiring date', $dt_value, $type, 'max:20|is_date:true' );

            case 'date_of_birth':
                return $this->validate_field( 'Date of birth', $dt_value, $type, 'max:20|is_date:true' );

            case 'reporting_to':
                return $this->validate_field( 'Reporting to', $dt_value, $type, 'max:20|' );

            case 'pay_rate':
                return $this->validate_field( 'Pay rate', $dt_value, $type, 'is_valid_amount:true' );

            case 'type':
                return $this->validate_field( 'Type', $dt_value, $type, 'max:20|' );

            case 'pay_type':
                return $this->validate_field( 'Pay type', $dt_value, $type, 'max:20|' );

            case 'status':
                return $this->validate_field( 'Status', $dt_value, $type, 'max:10|' );

            case 'other_email':
                return $this->validate_field( 'Other email', $dt_value, $type, 'max:60|email:true' );

            case 'address':
                return $this->validate_field( 'Address', $dt_value, $type, 'max:200|' );

            case 'work_phone':
                return $this->validate_field( 'Work phone', $dt_value, $type, 'max:20|is_phone:true' );

            case 'gender':
                return $this->validate_field( 'Gender', $dt_value, $type, 'max:10|' );

            case 'marital_status':
                return $this->validate_field( 'Marital status', $dt_value, $type, 'max:20|' );

            case 'nationality':
                return $this->validate_field( 'Nationality', $dt_value, $type, 'max:30|' );

            case 'driving_license':
                return $this->validate_field( 'Driving licence', $dt_value, $type, 'max:30|' );

            case 'hobbies':
                return $this->validate_field( 'Hobbies', $dt_value, $type, 'max:255|' );

            case 'user_url':
                return $this->validate_field( 'User email', $dt_value, $type, 'max:600|' );

            case 'category_id':
                return $this->validate_field( 'Category ID', $dt_value, $type, 'not_empty:true' );

            case 'product_type_id':
                return $this->validate_field( 'Product type ID', $dt_value, $type, 'not_empty:true' );

            default:
                return apply_filters( 'validate_field', [], $dt_key, $dt_value, $type );

        }
    }

    /**
     * Validate individual field data
     *
     * @since 1.6.5
     * @since 1.6.9 Added validation for name, zip code, and employee id
     *
     * @return array
     */
    public function validate_field( $field_name, $field_value, $type, $rulesets ) {
        $errors     = [];
        $rulesets   = explode( '|', $rulesets );

        if ( is_array( $rulesets ) ) {
            foreach ( $rulesets as $ruleset ) {
                $ruleset = explode( ':', $ruleset );

                if ( is_array( $ruleset ) && isset( $ruleset[0] ) && isset( $ruleset[1] ) ) {
                    $rule_name  = $ruleset[0];
                    $rule_value = $ruleset[1];

                    switch ( $rule_name ) {
                        case 'not_empty':
                            if ( $rule_value == 'true' && empty( $field_value ) ) {
                                $errors[] = __( "{$field_name} can not be empty", 'erp' );
                            }
                            break;

                        case 'max':
                            if ( strlen( $field_value ) > $rule_value ) {
                                $errors[] = __( "{$field_name} can not be more than {$rule_value} charecters", 'erp' );
                            }
                            break;

                        case 'min':
                            if ( ! empty( $field_value ) && strlen( $field_value ) < $rule_value ) {
                                $errors[] = __( "{$field_name} can not be less than {$rule_value} charecters", 'erp' );
                            }
                            break;

                        case 'email':
                            if ( $rule_value == 'true' && ! is_email( $field_value ) && ! empty( $field_value ) ) {
                                $errors[] = __( "{$field_name} should be a valid email", 'erp' );
                            }
                            break;

                        case 'is_date':
                            if ( $rule_value == 'true' ) {
                                $check_is_date = $this->is_valid_date( $rule_value, $field_value, $field_name );
                                if ( $check_is_date ) {
                                    $errors[] = $check_is_date;
                                }
                            }
                            break;

                        case 'is_phone':
                            if ( $rule_value === 'true' && ! empty( $field_value ) && ! erp_is_valid_contact_no( $field_value ) ) {
                                if ( false !== strpos( $field_value, 'E' ) ) {
                                    $errors[] = __( "The input ({$field_value}) for {$field_name} may be exponential. Please change your number in proper format.", 'erp' );
                                } else {
                                    $errors[] = __( "{$field_name} is not valid. Minimum 4 and maximum 18 digits are expected.", 'erp' );
                                }
                            }
                            break;

                        case 'not_csv_column_duplicate':
                            $check_is_duplicate_column = $this->is_duplicate_column( $rule_value, $field_value, $field_name );
                            if ( $check_is_duplicate_column ) {
                                $errors[] = $check_is_duplicate_column;
                            }
                            break;

                        case 'unique':
                            switch ( $type ) {
                                case 'employee':
                                    $check_is_unique_emp = $this->check_unique_employee( $rule_value, $field_value, $field_name );

                                    if ( $check_is_unique_emp ) {
                                        $errors[] = $check_is_unique_emp;
                                    }
                                    break;

                                case 'contact':
                                case 'company':
                                case 'customer':
                                case 'vendor':
                                    $check_is_unique_cont = $this->check_unique_contact( $rule_value, $field_value, $field_name );

                                    if ( $check_is_unique_cont ) {
                                        $errors[] = $check_is_unique_cont;
                                    }
                                    break;

                                case 'product':
                                    $check_is_unique_cont = $this->check_unique_product( $rule_value, $field_value, $field_name );

                                    if ( $check_is_unique_cont ) {
                                        $errors[] = $check_is_unique_cont;
                                    }
                                    break;

                                case 'product_non_unique':
                                    break;
                            }

                            break;

                        case 'is_valid_name':
                            if ( $rule_value === 'true' ) {
                                switch ( $type ) {
                                    case 'company':
                                    case 'city':
                                        if ( ! empty( $field_value ) && erp_contains_disallowed_chars( $field_value ) ) {
                                            $errors[] = __( "{$field_name} should not contain special charecters like %;\"=<>\/*+?$^{}[]", 'erp' );
                                        }
                                        break;

                                    case 'product':
                                    case 'product_non_unique':
                                        break;

                                    default:
                                        if ( ! empty( $field_value ) && ! erp_is_valid_name( $field_value ) ) {
                                            $errors[] = __( "{$field_name} should not contain digits and special charecters like !_@%#&:;\"=<>\/*+?$^{}[]", 'erp' );
                                        }
                                }
                            }
                            break;

                        case 'is_valid_emp_id':
                            if ( $rule_value === 'true' && ! empty( $field_value ) && ! erp_is_valid_employee_id( $field_value ) ) {
                                $errors[] = __( "{$field_name} is not valid. It should start with letter or digit and may contain letters, digits and hyphen (-) only", 'erp' );
                            }
                            break;

                        case 'zip':
                            if ( $rule_value === 'true' && ! empty( $field_value ) && ! erp_is_valid_zip_code( $field_value ) ) {
                                $errors[] = __( "{$field_name} is not valid. It should start with letter or digit and may contain letters, digits, space and hyphen (-) only", 'erp' );
                            }
                            break;

                        case 'is_valid_amount':
                            if ( $rule_value === 'true' && ! empty( $field_value ) && ! erp_is_valid_currency_amount( $field_value ) ) {
                                $errors[] = __( "{$field_name} is not valid. It may contain integer or decimal point values with maximum 2 digits after decimal point.", 'erp' );
                            }
                            break;

                        case 'is_valid_url':
                            if ( $rule_value === 'true' && ! empty( $field_value ) && ! erp_is_valid_url( $field_value ) ) {
                                $errors[] = __( "{$field_name} is not valid. Please provide a valid one.", 'erp' );
                            }
                            break;

                        default:
                            $custom_error_check = apply_filters( 'custom_validation', $rule_value, $field_value, $field_name, $type );

                            if ( $custom_error_check ) {
                                $errors[] = $custom_error_check;
                            }
                    }
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
    public function check_unique_contact( $column, $value, $field_name ) {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}erp_peoples WHERE $column = %s",
                [ $value ]
            )
        );

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
    public function check_unique_employee( $column, $value, $field_name ) {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_employees as emp LEFT JOIN {$wpdb->prefix}users as users ON emp.user_id = users.ID WHERE $column = %s",
                $value
            )
        );

        if ( $result > 0 ) {
            return __( "{$field_name} already exists. Try different one", 'erp' );
        }
    }

    /**
     * Check product specific field is unique or not
     *
     * @since 1.9.0
     *
     * @return string
     */
    public function check_unique_product( $column, $value, $field_name ) {
        global $wpdb;

        $result =  $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}erp_acct_products WHERE $column = %s",
                [ $value ]
            )
        );

        if ( $result > 0 ) {
            return __( "{$field_name} already exists. Try different one", 'erp' );
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
        if ( ! preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $value ) && ! empty( $value ) ) {
            return __( "{$field_name} should be a valid date. Ex: YYYY-MM-DD", 'erp' );
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
        if ( ! preg_match( '/[0-9]{2}+[0-9]{4}/s', $value ) && ! empty( $value ) ) {
            return __( "{$field_name} should be a valid phone/mobile no. Ex. 123456", 'erp' );
        }
    }

    /**
     * Check csv column is duplicate or not
     *
     * @since 1.6.5
     *
     * @return string
     */
    public function is_duplicate_column( $column, $value, $field_name ) {
        $csv_data    = $this->csv_data;
        $column_vals = wp_list_pluck( $csv_data, $column );
        $indexes     = array_keys( $column_vals, $value, true );

        if ( count( $indexes ) > 1 ) {
            return __( "Duplicate {$field_name} found at this file", 'erp' );
        }
    }

    /**
     * Validates custom fields
     *
     * @since 1.6.9
     *
     * @param string $dt_key
     * @param string $dt_value
     * @param string $type
     *
     * @return mixed
     */
    public function validate_custom_field( $dt_key, $dt_value, $type ) {
        switch ( $dt_key ) {
            default:
                return 0;
        }
    }
}
