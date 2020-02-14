<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Admin\Models\Company_Locations;
use WeDevs\ERP\HRM\Models\Department as HRDepartment;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\HRM\Models\Employee_History;
use WeDevs\ERP\HRM\Models\Hr_User;
use WeDevs\ERP\HRM\Models\Leave_Entitlement;
use WeDevs\ERP\HRM\Models\Leave_Holiday;
use WeDevs\ERP\HRM\Models\Leave_Policies;
use WeDevs\ERP\HRM\Models\Work_Experience;

class Employee {

    /**
     * user id
     *
     * @var int
     */
    public $user_id;
    /**
     * wp user \WP_User
     *
     * @type object
     */
    protected $wp_user;

    /**
     * Employee Model Instance
     *
     * @var object
     */
    protected $erp_user;

    /**
     * @var string
     */
    protected $erp_user_model = "\\WeDevs\\ERP\\HRM\\Models\\Employee";

    /**
     * Employee data
     *
     * @var array
     */
    protected $data = array(
        'user_id'    => '',
        'user_email' => '',
        'work'       => array(
            'employee_id'      => '',
            'designation'      => 0,
            'department'       => 0,
            'location'         => '',
            'hiring_source'    => '',
            'hiring_date'      => '',
            'termination_date' => '',
            'date_of_birth'    => '',
            'reporting_to'     => 0,
            'pay_rate'         => '',
            'pay_type'         => '',
            'type'             => '',
            'status'           => '',
        ),
        'personal'   => array(
            'photo_id'        => 0,
            'first_name'      => '',
            'middle_name'     => '',
            'last_name'       => '',
            'other_email'     => '',
            'phone'           => '',
            'work_phone'      => '',
            'blood_group'     => '',
            'spouse_name'     => '',
            'father_name'     => '',
            'mother_name'     => '',
            'mobile'          => '',
            'address'         => '',
            'gender'          => '',
            'marital_status'  => '',
            'nationality'     => '',
            'driving_license' => '',
            'hobbies'         => '',
            'user_url'        => '',
            'description'     => '',
            'street_1'        => '',
            'street_2'        => '',
            'city'            => '',
            'country'         => '',
            'state'           => '',
            'postal_code'     => '',
        ),
        'additional' => array()
    );
    /**
     * @var array
     */
    protected $restricted_data;

    /**
     * @since 1.0.0
     * @var array
     */
    protected $changes = array();

    /**
     * Employee constructor.
     *
     * @param null $employee
     */
    public function __construct( $employee = null ) {
        $this->user_id         = 0;
        $this->wp_user         = new \WP_User();
        $this->erp_user        = new $this->erp_user_model;
        $this->restricted_data = [];

        if ( $employee != null ) {
            $this->load_employee( $employee );
        }
    }

    /**
     * Magic method to get item data values
     *
     * @param      $key
     * @param      $value
     */
    public function __set( $key, $value ) {
        if ( is_callable( array( $this, "set_{$key}" ) ) ) {
            $this->{"set_{$key}"}( $value );
        } elseif ( array_key_exists( $key, $this->data['work'] ) ) {
            $this->changes['work'][ $key ] = $value;
        } else {
            $this->changes['personal'][ $key ] = $value;
        }
    }

    /**
     * Magic method to get item data values
     *
     * @param  string
     *
     * @return string
     */
    public function __get( $key ) {
        if ( in_array( $key, $this->restricted_data ) ) {
            return null;
        }
        if ( is_callable( array( $this, "get_{$key}" ) ) ) {
            return $this->{"get_{$key}"}();
        } elseif ( isset( $this->$key ) ) {
            return stripslashes( $this->key );
        } elseif ( isset( $this->erp_user->$key ) ) {
            return stripslashes( $this->erp_user->$key );
        } elseif ( isset( $this->wp_user->$key ) ) {
            return stripslashes( $this->wp_user->$key );
        } else {
            return null;
        }
    }


    /**
     * Load employee
     *
     * @param $employee
     *
     * @return mixed|void
     */
    protected function load_employee( $employee ) {
        if ( is_numeric( $employee ) ) {

            $user = get_user_by( 'id', $employee );

            if ( $user ) {
                $this->wp_user = $user;
                $this->user_id = $employee;
            }

        } elseif ( is_a( $employee, 'WP_User' ) ) {
            $this->wp_user = $employee;
            $this->user_id = $employee->ID;

        } elseif ( is_email( $employee ) ) {

            $user = get_user_by( 'email', $employee );
            if ( $user ) {
                $this->wp_user = $user;
                $this->user_id = $user->ID;
            }

        }

        if ( $this->user_id ) {
            $this->restricted_data = $this->get_restricted_employee_data();

            $employee_model = $this->erp_user_model;
            $erp_user       = $employee_model::withTrashed()->where( 'user_id', $this->user_id )->first();
            if ( $erp_user ) {
                $this->erp_user = $erp_user;
            }

            if ( $this->is_employee() ) {

                $this->data['user_id']    = $this->user_id;
                $this->data['user_email'] = $this->wp_user->user_email;

                foreach ( $this->data['work'] as $key => $value ) {
                    $this->data['work'][ $key ] = $this->$key;
                }

                foreach ( $this->data['personal'] as $key => $value ) {
                    $this->data['personal'][ $key ] = $this->$key;
                }
                $this->data['personal']['full_name'] = $this->get_full_name();
            }
        }
    }

    /**
     *
     * @param array $args
     *
     * @return $this|int|\WP_Error
     */
    public function create_employee( $args = array() ) {
        $posted = array_map( 'strip_tags_deep', $args );
        $data   = array_map( 'trim_deep', $posted );

        $data = wp_parse_args( $data, $this->data );

        //if is set employee id
        $employee_id        = null;
        $user_id            = ! empty( $data['user_id'] ) ? $data['user_id'] : null;
        $user_email         = ! empty( $data['user_email'] ) ? strtolower( $data['user_email'] ) : null;
        $erp_user           = null;
        $wp_user            = null;
        $data['user_email'] = $user_email;

        if ( ! empty( $data['work']['employee_id'] ) ) {
            $employee_id = intval( $data['work']['employee_id'] );
        }
        if ( ! empty( $data['personal']['employee_id'] ) ) {
            $employee_id = intval( $data['personal']['employee_id'] );
        }

        //if duplicate employee id
        if ( $employee_id && $employee_id != $this->employee_id ) {
            $erp_user = \WeDevs\ERP\HRM\Models\Employee::where( 'employee_id', $employee_id )->first();
            if ( $erp_user ) {
                return new \WP_Error( 'employee-id-exist', sprintf( __( 'Employee with the employee id %s already exist. Please use different one.', 'erp' ), $employee_id ) );
            }
        }

        //if we received user_id
        if ( $user_id ) {
            $wp_user = get_user_by( 'ID', $user_id );
        }

        //if wp user is not initiated yet and we received email
        if ( ! $wp_user && $user_email ) {
            $wp_user = get_user_by( 'email', $user_email );
            if ( $wp_user && ! is_wp_error( $wp_user ) ) {
                $user_id = $wp_user->ID;
            }
        }


        //if yet not wp user found then we have to create wp user and erp user
        if ( empty( $data['personal']['first_name'] ) ) {
            return new \WP_Error( 'empty-first-name', __( 'Please provide the first name.', 'erp' ) );
        }
        if ( empty( $data['personal']['last_name'] ) ) {
            return new \WP_Error( 'empty-last-name', __( 'Please provide the last name.', 'erp' ) );
        }
        if ( ! is_email( $data['user_email'] ) ) {
            return new \WP_Error( 'invalid-email', __( 'Please provide a valid email address.', 'erp' ) );
        }
        $first_name  = isset( $data['personal']['first_name'] ) ? $data['personal']['first_name'] : '';
        $middle_name = isset( $data['personal']['middle_name'] ) ? $data['personal']['middle_name'] : '';
        $last_name   = isset( $data['personal']['last_name'] ) ? $data['personal']['last_name'] : '';


        if ( ! $wp_user ) {
            $userdata = array(
                'user_login'   => $data['user_email'],
                'user_email'   => $data['user_email'],
                'first_name'   => $data['personal']['first_name'],
                'last_name'    => $data['personal']['last_name'],
                'user_url'     => isset( $data['personal']['user_url'] ) ? $data['personal']['user_url'] : '',
                'display_name' => $first_name . ' ' . $middle_name . ' ' . $last_name,
                'user_pass'    => wp_generate_password( 12 ),
                'role'         => 'employee',
            );

            $userdata = apply_filters( 'erp_hr_employee_args', $userdata );
            $user_id  = wp_insert_user( $userdata );
            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }
        }


        // inserting the user for the first time
        $work                = $data['work'];
        $hiring_date         = ! empty( $data['work']['hiring_date'] ) ? $data['work']['hiring_date'] : current_time( 'mysql' );
        $work['hiring_date'] = $hiring_date;
        $pay_type            = ( ! empty( $work['pay_type'] ) ) ? $work['pay_type'] : 'monthly';
        $work['pay_type']    = $pay_type;

        $erp_user = \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id )->first();

        //if user_id and erp_user is found then load user and update data
        if ( $wp_user && $erp_user ) {
            $this->load_employee( absint( $user_id ) );
            do_action( 'erp_hr_employee_new', $user_id, $data );
            return $this->update_employee( $data );
        }

        if ( ! $erp_user ) {
            \WeDevs\ERP\HRM\Models\Employee::create( [
                'user_id'     => $user_id,
                'designation' => 0,
                'department'  => 0,
                'status'      => 'active'
            ] );
        }

        // if reached here, seems like we have success creating the user
        $this->load_employee( $user_id );


        if ( ! empty( $work['type'] ) ) {

            $this->update_employment_status( [
                'type' => $work['type'],
                'date' => $work['hiring_date'],
            ] );
        }

        // update compensation
        if ( ! empty( $work['pay_rate'] ) ) {
            $this->update_compensation( [
                'comment'  => '',
                'pay_type' => $work['pay_type'],
                'reason'   => '',
                'pay_rate' => $work['pay_rate'],
                'date'     => $work['hiring_date'],
            ] );
        }

        // update job info
        $this->update_job_info( [
            'date'         => $work['hiring_date'],
            'designation'  => $work['designation'],
            'department'   => $work['department'],
            'reporting_to' => ! empty( $work['reporting_to'] ) ? $work['reporting_to'] : 0,
            'location'     => ! empty( $work['location'] ) ? $work['location'] : - 1,
        ] );

        $this->update_employee( array_merge( $data['work'], $data['personal'], $data['additional'] ) );
        do_action( 'erp_hr_employee_new', $this->id, $data );

        return $this;
    }

    /**
     * Update employee
     *
     * @since 1.3.0
     *
     * @param array $data
     *
     * @return $this | \WP_Error
     */
    public function update_employee( $data = array() ) {
        $restricted = [
            'user_id',
            'user_url',
            'id',
            'ID',
        ];


        $posted = array_map( 'strip_tags_deep', $data );
        $posted = erp_array_flatten( $posted );
        $posted = array_except( $posted, $restricted );

        //update user email
        if ( isset( $posted['user_email'] )
             && $posted['user_email'] !== $this->wp_user->user_email
             && filter_var( $posted['user_email'], FILTER_VALIDATE_EMAIL )
             && ! get_user_by( 'user_email', $posted['user_email'] ) ) {
            $user_email = esc_attr( $posted['user_email'] );
            $result     = wp_update_user( array(
                'ID'         => $this->user_id,
                'user_email' => strtolower( $user_email ),
            ) );

            if ( is_wp_error( $result ) ) {
                return $result;
            }
        }

        //check if something removed
        foreach ( $this->data['personal'] as $p_key => $p_val ) {
            if ( empty( $posted[ $p_key ] ) ) {
                $this->changes['personal'][ $p_key ] = '';
            }
        }


        foreach ( $posted as $key => $value ) {
            if ( ! empty( $value ) && ( $this->$key != $value ) ) {
                if ( array_key_exists( $key, $this->data['work'] ) ) {
                    $this->changes['work'][ $key ] = $value;
                } else {
                    $this->changes['personal'][ $key ] = $value;
                }
            }
        }

        if ( ! empty( $data['additional'] ) ) {
            $this->changes['additional'] = $data['additional'];
        } else {
            $this->changes['additional'] = [];
        }

        if ( empty( $this->changes ) ) {
            return $this;
        }

        do_action( 'erp_hr_employee_update', $this->user_id, wp_parse_args( $this->data, $this->changes ) );

        if ( ! empty( $this->changes['work'] ) ) {
            $this->erp_user->update( $this->changes['work'] );
        }

        if ( ! empty( $this->changes['personal'] ) ) {
            foreach ( $this->changes['personal'] as $key => $value ) {
                update_user_meta( $this->id, $key, $value );
            }
        }

        //reset changes

        $this->changes = array();

        return $this;
    }


    /**
     * Get employee data
     *
     * @since 1.3.0
     *
     * @param array $data
     * @param       $flat boolean
     *
     * @return array
     */
    function get_data( $data = array(), $flat = false ) {
        $employee_data = array_merge( $data, $this->data );

        $employee_data = apply_filters( 'erp_hr_get_employee_fields', $employee_data, $this->user_id, $this->wp_user );
        if ( $flat ) {
            $employee_data = erp_array_flatten( $employee_data );
        }

        return $employee_data;
    }

    /**
     *  Get the user info as an array
     *
     * @deprecated 1.3.0
     * @return array|mixed|string
     */
    public function to_array() {
        //backward compatibility
        $data['id'] = $this->user_id;

        $data['user_id']         = $this->user_id;
        $data['employee_id']     = $this->employee_id;
        $data['user_email']      = $this->user_email;
        $data['name']            = array(
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'middle_name' => $this->middle_name,
            'full_name'   => $this->get_full_name()
        );
        $avatar_id               = $this->get_photo_id();
        $data['avatar']['id']    = $avatar_id;
        $data['avatar']['image'] = $this->get_avatar();
        if ( $avatar_id ) {
            $data['avatar']['url'] = $this->get_avatar_url( $avatar_id );
        }

        return $this->get_data( $data );
    }


    /**
     * Checks whether the use is employee or not
     *
     * @since 1.3.0
     * @return bool
     */
    public function is_employee() {
        //exceptional case for admin
        if ( in_array( 'administrator', $this->get_roles()['roles'] ) ) {
            return true;
        }

        if ( $this->erp_user ) {
            return true;
        }

        return false;
    }

    /**
     * Get user id
     *
     * @since 1.3.0
     * @return int
     */
    public function get_user_id() {
        return $this->user_id;
    }

    /**
     * Get user id
     *
     * @deprecated 1.3.0
     * @return int
     */
    public function get_id() {
        return $this->get_user_id();
    }

    public function get_erp_user() {
        return $this->erp_user;
    }

    /**
     * get photo id
     *
     * @return int|null
     */
    public function get_photo_id() {
        if ( isset( $this->wp_user->photo_id ) ) {
            return (int) $this->wp_user->photo_id;
        }

        return null;
    }

    /**
     * Get an employee avatar
     *
     * @param  integer  avatar size in pixels
     *
     * @return string   image with HTML tag
     */
    public function get_avatar_url( $size = 32 ) {
        $photo_id = $this->get_photo_id();

        if ( $this->user_id && ! empty( $photo_id ) ) {
            return wp_get_attachment_url( $this->photo_id );
        }

        return get_avatar_url( $this->user_id, [ 'size' => $size ] );
    }

    /**
     * Get an employee avatar url
     *
     * @param  integer  avatar size in pixels
     *
     * @return string   image with HTML tag
     */
    public function get_avatar( $size = 32 ) {
        $photo_id = $this->get_photo_id();

        if ( $this->user_id && ! empty( $photo_id ) ) {
            $image = wp_get_attachment_thumb_url( $this->photo_id );

            return sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
        }

        $avatar = get_avatar( $this->user_id, $size );

        if ( ! $avatar ) {
            $image  = WPERP_ASSETS . '/images/mystery-person.png';
            $avatar = sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
        }

        return $avatar;
    }

    /**
     * Get single employee page view url
     *
     * @return string the url
     */
    public function get_details_url() {
        if ( $this->user_id ) {
            return admin_url( 'admin.php?page=erp-hr&section=employee&action=view&id=' . $this->user_id );
        }
    }

    /**
     * Get user url
     *
     * @return string
     */
    public function get_user_url() {
        return $this->get_details_url();
    }

    /**
     * Get the employees full name
     *
     * @return string
     */
    public function get_full_name() {
        $name = array();
        if ( $this->first_name ) {
            $name[] = $this->first_name;
        }

        if ( $this->middle_name ) {
            $name[] = $this->middle_name;
        }

        if ( $this->last_name ) {
            $name[] = $this->last_name;
        }

        return implode( ' ', $name );
    }

    /**
     * Get an HTML link to single employee view
     *
     * @return string url to details
     */
    public function get_link() {
        return sprintf( '<a href="%s">%s</a>', $this->get_details_url(), $this->get_full_name() );
    }

    /**
     * Get designation
     *
     * @since 1.3.0
     *
     * @param string $context
     *
     * @return mixed|string
     */
    public function get_designation( $context = 'edit' ) {
        if ( $this->is_employee() && isset( $this->erp_user->designation ) ) {
            if ( $context == 'edit' ) {
                return $this->erp_user->designation;
            }
            $designation = Designation::find( $this->designation );
            if ( $designation ) {
                return stripslashes( $designation->title );
            }
        }
    }

    /**
     * Get the job title
     *
     * @since 1.3.0
     * @return string
     */
    public function get_job_title() {
        return $this->get_designation( 'view' );
    }

    /**
     * Get department
     *
     * @since 1.3.0
     *
     * @param string $context
     *
     * @return mixed|string
     */
    public function get_department( $context = 'edit' ) {
        if ( $this->is_employee() && isset( $this->erp_user->department ) ) {
            if ( $context == 'edit' ) {
                return $this->erp_user->department;
            }
            $department = HRDepartment::find( $this->department );
            if ( $department ) {
                return stripslashes( $department->title );
            }
        }
    }

    /**
     * Get the department title
     *
     * @deprecated 1.3.0
     * @return string
     */
    public function get_department_title() {
        return $this->get_department( 'view' );
    }

    /**
     * Get the work location title
     *
     * @return string
     */
    public function get_work_location() {
        return $this->get_location( 'view' );
    }

    /**
     * Get the work location id
     *
     * @since 1.2.0
     *
     * @return int
     */
    public function get_location( $context = 'edit' ) {
        if ( $this->is_employee() && isset( $this->erp_user->location ) ) {
            if ( $context == 'edit' ) {
                return $this->erp_user->location;
            }
            $location = Company_Locations::find( $this->location );
            if ( $location ) {
                return stripslashes( $location->name );
            }
        }
    }

    /**
     * Get the employee status
     *
     * @return string
     */
    public function get_status( $context = 'edit' ) {
        if ( isset( $this->erp_user->status ) ) {
            $status = $this->erp_user->status;
            if ( $context == 'edit' ) {
                return $status;
            }
            $statuses = erp_hr_get_employee_statuses();

            if ( array_key_exists( $status, $statuses ) ) {
                return $statuses[ $status ];
            }
        }
    }

    /**
     * Get the employee type
     *
     * @param string $context
     *
     * @return mixed
     */
    public function get_type( $context = 'edit' ) {
        if ( $this->is_employee() && isset( $this->erp_user->type ) ) {
            $type = $this->erp_user->type;
            if ( $context == 'edit' ) {
                return $type;
            }

            $types = erp_hr_get_employee_types();

            if ( array_key_exists( $this->erp_user->type, $types ) ) {
                return $types[ $this->erp_user->type ];
            }
        }
    }

    /**
     * Get the employee work_hiring_source
     *
     * @return string
     */
    public function get_hiring_source( $context = 'edit' ) {
        if ( $this->is_employee() && ! empty( $this->erp_user->hiring_source ) ) {
            if ( $context == 'edit' ) {
                return $this->erp_user->hiring_source;
            }
            $sources = erp_hr_get_employee_sources();

            if ( array_key_exists( $this->erp_user->hiring_source, $sources ) ) {
                return $sources[ $this->erp_user->hiring_source ];
            }
        }
    }

    /**
     * Get the employee gender
     *
     * @return string
     */
    public function get_gender( $context = 'edit' ) {
        if ( $this->is_employee() && ! empty( $this->wp_user->gender ) ) {
            if ( $context == 'edit' ) {
                return $this->wp_user->gender;
            }
            $genders = erp_hr_get_genders();

            if ( array_key_exists( $this->wp_user->gender, $genders ) ) {
                return $genders[ $this->wp_user->gender ];
            }
        }

        return null;
    }

    /**
     * Get the marital status
     *
     * @return string
     */
    public function get_marital_status( $context = 'edit' ) {
        if ( $this->is_employee() && ! empty( $this->wp_user->marital_status ) ) {
            if ( $context == 'edit' ) {
                return $this->wp_user->marital_status;
            }

            $statuses = erp_hr_get_marital_statuses();
            if ( array_key_exists( $this->wp_user->marital_status, $statuses ) ) {
                return $statuses[ $this->wp_user->marital_status ];
            }
        }

        return null;
    }


    /**
     * Get the employee nationalit
     *
     * @return string
     */
    public function get_nationality( $context = 'edit' ) {
        if ( $this->is_employee() && ! empty( $this->wp_user->nationality ) ) {
            if ( $context == 'edit' ) {
                return $this->wp_user->nationality;
            }

            $countries = \WeDevs\ERP\Countries::instance()->get_countries();

            if ( array_key_exists( $this->wp_user->nationality, $countries ) ) {
                return $countries[ $this->wp_user->nationality ];
            }
        }
    }

    /**
     * Set hiring date
     *
     * @param $date
     */
    public function set_hiring_date( $date ) {
        if ( ! empty( $date ) ) {
            $this->changes['work']['hiring_date'] = date( 'Y-m-d', strtotime( $date ) );
        }
    }

    /**
     * get hiring date
     *
     * @since 1.3.0
     * @return string
     */
    public function get_hiring_date() {
        if ( isset( $this->erp_user->hiring_date )
             && is_valid_date( $this->erp_user->hiring_date )
             && $this->erp_user->hiring_date != '0000-00-00' ) {
            return erp_format_date( $this->erp_user->hiring_date );
        }
    }

    /**
     * Get joined date
     *
     * @deprecated
     * @return string
     */
    public function get_joined_date() {
        return $this->get_hiring_date();
    }


    public function get_date_of_birth() {
        $date = null;
        if ( isset( $this->erp_user->date_of_birth )
             && is_valid_date( $this->erp_user->date_of_birth )
             && ( $this->erp_user->date_of_birth != '0000-00-00' ) ) {
            $date = erp_format_date( $this->erp_user->date_of_birth );
        }

        return $date;
    }

    public function set_date_of_birth( $date ) {
        if ( ! empty( $date ) ) {
            $this->changes['work']['date_of_birth'] = date( 'Y-m-d', strtotime( $date ) );
        }
    }

    /**
     * Get Street 1
     *
     * @return string
     */
    public function get_street_1() {
        if ( ! empty( $this->wp_user->street_1 ) ) {
            return $this->wp_user->street_1;
        }
    }

    /**
     * Get Street 2
     *
     * @return string
     */
    public function get_street_2() {
        if ( ! empty( $this->wp_user->street_2 ) ) {
            return $this->wp_user->street_2;
        }
    }

    /**
     * Get City
     *
     * @return string
     */
    public function get_city() {
        if ( ! empty( $this->wp_user->city ) ) {
            return $this->wp_user->city;
        }
    }

    /**
     * Get Postal Code
     *
     * @return string
     */
    public function get_postal_code() {
        if ( ! empty( $this->wp_user->postal_code ) ) {
            return $this->wp_user->postal_code;
        }
    }

    /**
     * Get Country
     *
     * @return string
     */
    public function get_country( $context = 'edit' ) {
        if ( $this->is_employee() && isset( $this->wp_user->country ) ) {
            if ( $context == 'edit' ) {
                return $this->wp_user->country;
            }

            return erp_get_country_name( $this->wp_user->country );
        }
    }


    /**
     * Get State
     *
     * @since 1.3.0
     *
     * @param string $context
     *
     * @return mixed|string
     *
     */
    public function get_state( $context = 'edit' ) {
        if ( $this->is_employee() && isset( $this->wp_user->state ) ) {
            if ( $context == 'edit' ) {
                return $this->wp_user->state;
            }

            if ( $this->wp_user->country
                 && ( $this->wp_user->country !== '-1' )
                 && ( $this->wp_user->state !== '-1' )
                 && $this->wp_user->state ) {
                return erp_get_state_name( $this->wp_user->country, $this->wp_user->state );
            }
        }
    }

    /**
     * Get the name of reporting user
     * @since 1.3.2 return object
     *
     * @param $return_object boolean
     *
     * @return int $user_id
     */
    public function get_reporting_to() {
        if ( $this->is_employee() && isset( $this->erp_user->reporting_to ) ) {
            return (int) $this->erp_user->reporting_to;
        }
    }

    /**
     * Get a phone number
     *
     * @param  string  type of phone. work|mobile|phone
     *
     * @deprecated 1.3.2
     *
     * @return string
     */
    public function get_contact_details( $which = 'work' ) {
        $phone = '';

        switch ( $which ) {
            case 'mobile':
                $phone = isset( $this->wp_user->mobile ) ? $this->wp_user->mobile : '';
                break;

            case 'phone':
                $phone = isset( $this->wp_user->phone ) ? $this->wp_user->phone : '';
                break;

            default:
                $phone = isset( $this->wp_user->work_phone ) ? $this->wp_user->work_phone : '';
                break;
        }

        return $phone;
    }


    public function get_phone() {
        if ( $this->is_employee() && isset( $this->wp_user->phone ) ) {
            return $this->wp_user->phone;
        }
    }

    public function get_work_phone() {
        if ( $this->is_employee() && isset( $this->wp_user->work_phone ) ) {
            return $this->wp_user->work_phone;
        }
    }

    public function get_mobile() {
        if ( $this->is_employee() && isset( $this->wp_user->mobile ) ) {
            return $this->wp_user->mobile;
        }
    }

    public function get_driving_license() {
        if ( $this->is_employee() && isset( $this->wp_user->driving_license ) ) {
            return $this->wp_user->driving_license;
        }
    }

    /**
     * Get blood group
     * 
     * @since 1.5.12
     * @return string
     */
    public function get_bloog_group() {
        if ( $this->is_employee() && isset( $this->wp_user->blood_group ) ) {
            if ( $this->wp_user->blood_group === '-1' ) {
                return '';
            }

            return strtoupper( $this->wp_user->blood_group );
        }
    }

    /**
     * Get birth date
     *
     * @deprecated 1.3.0
     * @return string
     */
    public function get_birthday() {
        return $this->get_date_of_birth();
    }

    /**
     * Get educational qualifications
     *
     * @return array the qualifications
     */
    public function get_educations( $limit = 30, $offset = 0 ) {
        return $this->erp_user
            ->educations()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }

    /**
     * Create / Update Education
     *
     * @since 1.3.0
     *
     * @param array $data
     * @param bool $return_id
     *
     * @return array|\WP_Error
     */
    public function add_education( $data, $return_id = true ) {
        $default = [
            'id'       => '',
            'school'   => '',
            'degree'   => '',
            'field'    => '',
            'finished' => '',
            'notes'    => '',
            'interest' => ''
        ];

        $args = wp_parse_args( $data, $default );

        $requires = [
            'school'   => __( 'School Name', 'erp' ),
            'degree'   => __( 'Degree', 'erp' ),
            'field'    => __( 'Field', 'erp' ),
            'finished' => __( 'Completion date', 'erp' ),
        ];

        foreach ( $requires as $key => $value ) {
            if ( empty( $args[ $key ] ) ) {
                return $this->send_error( "empty-" . $key, __( sprintf( '%s is required.', $value ), 'erp' ) );
            }
        }

        if ( ! $args['id'] ) {
            // education will update
            $data            = $args;
            $data['user_id'] = $this->user_id;
            do_action( 'erp_hr_employee_education_create', $data );
        }

        $education = $this->erp_user->educations()->updateOrCreate( [ 'id' => $args['id'] ], $args )->toArray();

        if ( ! $education ) {
            return $this->send_error( 'error-creating-education', __( 'Could not create education.', 'erp' ) );
        } else {
            update_user_meta( $education['employee_id'], 'education_' . $education['employee_id'] . '_' . $education['id'], $data['expiration_date'] );
        }

        return $education;
    }


    /**
     * Delete Education
     *
     * @since 1.3.0
     *
     */
    public function delete_education( $id ) {
        return $this->erp_user->educations()->find( $id )->delete();
    }

    /**
     * Get dependents
     *
     * @return array the dependents
     */
    public function get_dependents( $limit = 30, $offset = 0 ) {
        return $this->erp_user
            ->dependents()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }

    /**
     * Add dependent
     *
     * @since 1.3.0
     *
     * @param array $data
     * @param bool $return_id
     *
     * @return array|\WP_Error
     */

    public function add_dependent( $data, $return_id = true ) {
        $default = [
            'id'       => '',
            'name'     => '',
            'relation' => '',
            'dob'      => ''
        ];

        $args = wp_parse_args( $data, $default );

        $requires = [
            'name'     => __( 'Name', 'erp' ),
            'relation' => __( 'Relation', 'erp' )
        ];

        foreach ( $requires as $key => $value ) {
            if ( empty( $args[ $key ] ) ) {
                return $this->send_error( "empty-" . $key, __( sprintf( '%s is required.', $value ), 'erp' ) );
            }
        }

        if ( isset( $args['dob'] ) && ! empty( $args['dob'] ) ) {
            if ( ! is_valid_date( $args['dob'] ) ) {
                return new \WP_Error( 'invalid-required-params', __( 'Invalid date format', 'erp' ) );
            }
            $args['dob'] = date( 'Y-m-d', strtotime( $args['dob'] ) );
        }


        if ( ! $args['id'] ) {
            // education will update
            $data            = $args;
            $data['user_id'] = $this->user_id;
            do_action( 'erp_hr_employee_dependents_create', $data );
        }

        $dependent = $this->erp_user->dependents()->updateOrCreate( [ 'id' => $args['id'] ], $args )->toArray();

        if ( ! $dependent ) {
            return $this->send_error( 'error-creating-dependent', __( 'Could not create dependent.', 'erp' ) );
        }

        return $dependent;
    }

    /**
     *  Delete dependent
     *
     * @since 1.3.0
     *
     */
    public function delete_dependent( $id ) {
        return $this->erp_user->dependents()->find( $id )->delete();
    }

    /**
     * Get work experiences
     *
     * @return array
     */
    public function get_experiences( $limit = 30, $offset = 0 ) {
        return $this->erp_user
            ->experiences()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }

    /**
     * Create / Update Experience
     *
     * @since 1.3.0
     *
     * @param array $data
     * @param bool $return_id
     *
     * @return array|\WP_Error
     */
    public function add_experience( array $data, $return_id = true ) {
        $default = [
            'id'           => '',
            'company_name' => '',
            'job_title'    => '',
            'from'         => '',
            'to'           => '',
            'description'  => ''
        ];

        $args = wp_parse_args( $data, $default );

        if ( empty( $args['company_name'] ) ) {
            return new \WP_Error( 'missing-required-params', __( 'Missing Company Name', 'erp' ) );
        }
        if ( empty( $args['job_title'] ) ) {
            return new \WP_Error( 'missing-required-params', __( 'Missing Job Title', 'erp' ) );
        }
        if ( empty( $args['from'] ) ) {
            return new \WP_Error( 'missing-required-params', __( 'Missing From Date', 'erp' ) );
        }
        if ( empty( $args['to'] ) ) {
            return new \WP_Error( 'missing-required-params', __( 'Missing To Date', 'erp' ) );
        }
        if ( ! is_valid_date( $args['from'] ) && $args['from'] ) {
            return new \WP_Error( 'invalid-required-params', __( 'Invalid date format', 'erp' ) );
        }
        $args['from'] = date( 'Y-m-d', strtotime( $args['from'] ) );
        $args['to']   = date( 'Y-m-d', strtotime( $args['to'] ) );

        $experience = $this->erp_user->experiences()->updateOrCreate( [ 'id' => $args['id'] ], $args )->toArray();


        if ( ! $experience ) {
            return $this->send_error( 'error-creating-experience', __( 'Could not create work experience.', 'erp' ) );
        }

        if ( $experience['id'] ) {
            $data            = (array) $experience;
            $data            = $args;
            $data['user_id'] = $this->user_id;
            do_action( 'erp_hr_employee_experience_new', $data );
        }

        return $experience;
    }

    /**
     * Remove Experience
     *
     * @since 1.3.0
     *
     * @param array $id
     *
     * @return array|\WP_Error
     */
    public function delete_experience( $id ) {
        return $this->erp_user->experiences()->find( $id )->delete();
    }

    /**
     * Get job histories
     *
     * @return array
     */
    public function get_job_histories( $module = 'all', $limit = 30, $offset = 0 ) {
        $modules   = erp_hr_employee_history_modules();
        $histories = $this->erp_user->histories();
        if ( ( $module !== 'all' ) && ( in_array( $module, $modules ) ) ) {
            $histories = $histories->where( 'module', $module );
        }

        $histories = $histories->skip( $offset )
                               ->take( $limit )
                               ->get();

        $formatted_histories = array();
        foreach ( $histories as $history ) {
            if ( $history['module'] == 'employment' ) {
                $item                                        = array(
                    'id'       => $history['id'],
                    'type'     => $history['type'],
                    'comments' => $history['comment'],
                    'date'     => $history['date'],
                    'module'   => $history['module'],
                );
                $formatted_histories[ $history['module'] ][] = $item;
            }
            if ( $history['module'] == 'compensation' ) {
                $item                                        = array(
                    'id'       => $history['id'],
                    'comment'  => $history['comment'],
                    'pay_type' => $history['category'],
                    'reason'   => $history['data'],
                    'pay_rate' => $history['type'],
                    'date'     => $history['date'],
                    'module'   => $history['module'],
                );
                $formatted_histories[ $history['module'] ][] = $item;
            }
            if ( $history['module'] == 'job' ) {
                $item                                        = array(
                    'id'           => $history['id'],
                    'date'         => $history['date'],
                    'designation'  => $history['comment'],
                    'department'   => $history['category'],
                    'reporting_to' => $history['data'],
                    'location'     => $history['type'],
                    'module'       => $history['module'],
                );
                $formatted_histories[ $history['module'] ][] = $item;
            }
        }

        return $formatted_histories;
    }

    /**
     * Delete employee's job history
     *
     * @since 1.3.0
     *
     * @param $id
     *
     * @return \WP_Error
     */
    public function delete_job_history( $id ) {
        $history = $this->erp_user->histories()->find( $id );
        if ( ! $history ) {
            return new \WP_Error( 'invalid-history-id', __( 'This job history does not exist or does not belongs to the supplied user', 'erp' ) );
        }

        $result = $history->delete();
        if ( $result ) {
            do_action( "erp_hr_employee_{$history->module}_history_delete", $id );

            return $result;
        }
    }

    /**
     * date employment status
     *
     * @param array $args
     *
     * @return array|\WP_Error
     */
    public function update_employment_status( $args = array() ) {
        $default = array(
            'id'       => '',
            'type'     => '',
            'comments' => '',
            'date'     => current_time( 'mysql' ),
        );

        $args = wp_parse_args( $args, $default );

        $types = erp_hr_get_employee_types();
        if ( empty( $args['type'] ) || ! array_key_exists( $args['type'], $types ) ) {
            return new \WP_Error( 'invalid-employment-type', __( 'Invalid Employment Type', 'erp' ) );
        }

        do_action( 'erp_hr_employee_employment_status_create', $this->get_user_id() );

        $this->erp_user->update( [
            'type' => $args['type']
        ] );

        $history = $this->get_erp_user()->histories()->updateOrCreate( [ 'id' => $args['id'] ], [
            'module'  => 'employment',
            'type'    => $args['type'],
            'comment' => $args['comments'],
            'date'    => $args['date']
        ] );

        return [
            'id'       => $history['id'],
            'type'     => $history['type'],
            'comments' => $history['comment'],
            'date'     => $history['date'],
            'module'   => $history['module'],
        ];
    }

    /**
     * Update compensation of the employee
     *
     * @param $args array
     *
     * @return array|bool|\WP_Error
     */
    public function update_compensation( $args = array() ) {
        $default = array(
            'id'       => '',
            'comment'  => '',
            'pay_type' => '',
            'reason'   => '',
            'pay_rate' => 0,
            'date'     => current_time( 'mysql' ),
        );

        $args      = wp_parse_args( $args, $default );
        $reasons   = erp_hr_get_pay_change_reasons();
        $pay_types = erp_hr_get_pay_type();

        if ( empty( $args['pay_type'] ) || ! array_key_exists( $args['pay_type'], $pay_types ) ) {
            return new \WP_Error( 'invalid-pay-type', __( 'Invalid Pay Type', 'erp' ) );
        }
        if ( empty( $args['pay_rate'] ) ) {
            return new \WP_Error( 'invalid-pay-rate', __( 'Invalid Pay Rate', 'erp' ) );
        }
        if ( !empty( $args['reason'] ) && ! array_key_exists( $args['reason'], $reasons ) ) {
            return new \WP_Error( 'invalid-reason', __( 'Invalid Reason Type', 'erp' ) );
        }

        do_action( 'erp_hr_employee_compensation_create', $this->get_user_id() );

        $this->erp_user->update( [
            'pay_rate' => floatval( $args['pay_rate'] ),
            'pay_type' => $args['pay_type']
        ] );

        $history = $this->get_erp_user()->histories()->updateOrCreate( [ 'id' => $args['id'] ], [
            'module'   => 'compensation',
            'category' => $args['pay_type'],
            'type'     => $args['pay_rate'],
            'comment'  => $args['comment'],
            'data'     => $args['reason'],
            'date'     => $args['date']
        ] );

        return [
            'id'       => $history['id'],
            'comment'  => $history['comment'],
            'pay_type' => $history['category'],
            'reason'   => $history['data'],
            'pay_rate' => $history['type'],
            'date'     => $history['date'],
            'module'   => $history['module'],
        ];
    }

    /**
     * update job info
     *
     * @param   $args array
     *
     * @return array|bool|\WP_Error
     */
    public function update_job_info( $args = array() ) {
        $default = array(
            'id'           => '',
            'date'         => current_time( 'mysql' ),
            'designation'  => '',
            'department'   => '',
            'reporting_to' => '',
            'location'     => '-1',
            'module'       => 'job',
        );

        $args = wp_parse_args( $args, $default );
//        if ( empty( $args['designation'] ) || ! is_numeric( $args['designation'] ) ) {
//            return new \WP_Error( 'invalid-designation-id', __( 'Invalid Designation Type', 'erp' ) );
//        }
//
//        if ( empty( $args['department'] ) || ! is_numeric( $args['department'] ) ) {
//            return new \WP_Error( 'invalid-department-id', __( 'Invalid Department Type', 'erp' ) );
//        }
//
//        if ( empty( $args['reporting_to'] ) || ! is_numeric( $args['reporting_to'] ) ) {
//            return new \WP_Error( 'invalid-reporting-to-user', __( 'Invalid Reporting To User', 'erp' ) );
//        }

        do_action( 'erp_hr_employee_job_info_create', $this->get_user_id() );

        $this->erp_user->update( [
            'designation'  => $args['designation'],
            'department'   => $args['department'],
            'reporting_to' => $args['reporting_to'],
            'location'     => $args['location'],
        ] );

        $history = $this->get_erp_user()->histories()->updateOrCreate( [ 'id' => $args['id'] ], [
            'date'     => $args['date'],
            'data'     => $args['reporting_to'],
            'category' => $this->get_department( 'view' ),
            'type'     => $this->get_location( 'view' ),
            'comment'  => $this->get_designation( 'view' ),
            'module'   => 'job',
        ] );

        return [
            'id'           => $history['id'],
            'date'         => $history['date'],
            'designation'  => $history['comment'],
            'department'   => $history['category'],
            'reporting_to' => $history['data'],
            'location'     => $history['type'],
            'module'       => $history['module'],
        ];

    }

    /**
     * Get employee performance list
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function get_performances( $type = 'all', $limit = 30, $offset = 0 ) {
        $types = [ 'reviews', 'comments', 'goals' ];

        $performances = $this->erp_user->performances();
        if ( ( $type !== 'all' ) && ( in_array( $type, $types ) ) ) {
            $performances = $performances->where( 'type', $type );
        }

        $performances = $performances->skip( $offset )
                                     ->take( $limit )
                                     ->get()
                                     ->groupBy( 'type' );

        return $performances;
    }

    /**
     * add Performance
     *
     * @param $args
     *
     * @return array|\WP_Error
     */
    public function add_performance( $args ) {
        $default = array(
            'id'                  => '',
            'reporting_to'        => '',
            'job_knowledge'       => '',
            'work_quality'        => '',
            'attendance'          => '',
            'communication'       => '',
            'dependablity'        => '',
            'reviewer'            => '',
            'comments'            => '',
            'completion_date'     => '',
            'goal_description'    => '',
            'employee_assessment' => '',
            'supervisor'          => '',
            'type'                => '',
            'performance_date'    => ( empty( $args['performance_date'] ) ) ? current_time( 'mysql' ) : $args['performance_date'],
        );
        $args    = wp_parse_args( $args, $default );

        if ( empty( $args['type'] ) || ! in_array( $args['type'], [ 'reviews', 'comments', 'goals' ] ) ) {
            return new \WP_Error( 'invalid-performance-type', __( 'Invalid Performance Type received', 'erp' ) );
        }

        if ( empty( $args['performance_date'] ) ) {
            return new \WP_Error( 'missing-required-params', __( 'Missing Date', 'erp' ) );
        }

        if ( ! is_valid_date( $args['performance_date'] ) && $args['performance_date'] ) {
            return new \WP_Error( 'invalid-required-params', __( 'Invalid date format', 'erp' ) );
        }

        if ( $args['type'] == 'reviews' ) {
            $reporting_to = new Employee( $args['reporting_to'] );
            if ( ! $reporting_to->is_employee() ) {
                return new \WP_Error( 'invalid-user-id', __( 'Invalid reporting to used.', 'erp' ) );
            }
        }

        if ( $args['type'] == 'comments' ) {
            $reviewer = new Employee( $args['reviewer'] );
            if ( ! $reviewer->is_employee() ) {
                return new \WP_Error( 'invalid-user-id', __( 'Invalid reviewer user.', 'erp' ) );
            }
        }

        if ( $args['type'] == 'goals' ) {

            if ( empty( $args['completion_date'] ) ) {
                return new \WP_Error( 'missing-required-params', __( 'Missing Date', 'erp' ) );
            }

            if ( ! is_valid_date( $args['completion_date'] ) && $args['completion_date'] ) {
                return new \WP_Error( 'invalid-required-params', __( 'Invalid date format', 'erp' ) );
            }

            $supervisor = new Employee( $args['supervisor'] );
            if ( ! $supervisor->is_employee() ) {
                return new \WP_Error( 'invalid-user-id', __( 'Invalid supervisor user.', 'erp' ) );
            }
        }

        return $this->get_erp_user()->performances()->updateOrCreate( [ 'id' => $args['id'] ], $args )->toArray();
    }

    /**
     * Delete performance
     *
     * @param $id
     *
     * @return \WP_Error
     */
    public function delete_performance( $id ) {
        $performance = $this->get_erp_user()->performances()->find( $id );
        if ( ! $performance ) {
            return new \WP_Error( 'invalid-note-id', __( 'This note does not exist or does not belongs to the supplied user', 'erp' ) );
        }

        return $performance->delete();
    }

    /**
     * Get all notes
     *
     * @param  int $limit
     * @param  int $offset
     *
     * @return array
     */
    public function get_notes( $limit = 30, $offset = 0 ) {
        return $this->erp_user
            ->notes()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }

    /**
     * Add note
     *
     * @param      $note
     * @param null $comment_by
     * @param bool $return_object
     *
     * @return bool|mixed|static
     */
    public function add_note( $note, $comment_by = null, $return_object = false ) {
        if ( $comment_by == null ) {
            $comment_by = get_current_user_id();
        }

        $data = array(
            'comment'    => $note,
            'comment_by' => $comment_by,
        );
        $note = $this->erp_user->notes()->create( $data );
        if ( $note ) {
            do_action( 'erp_hr_employee_note_new', $note->id, $this->user_id );

            if ( $return_object ) {
                return $note;
            }

            return $note->id;
        }

        return false;
    }

    /**
     * delete note
     *
     * @param $id
     *
     * @return \WP_Error
     */
    public function delete_note( $id ) {
        $note = $this->erp_user->notes()->find( $id );
        if ( ! $note ) {
            return new \WP_Error( 'invalid-note-id', __( 'This note does not exist or does not belongs to the supplied user', 'erp' ) );
        }

        return $note->delete();
    }

    /**
     * Get announcements
     *
     * @since 1.3.0
     *
     * @param null $date
     * @param bool $return_array
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function get_announcements( $date = null, $return_array = false, $limit = 30, $offset = 0 ) {
        global $wpdb;
        $announcements = \WeDevs\ERP\HRM\Models\Announcement::join( $wpdb->posts, 'post_id', '=', $wpdb->posts . '.ID' );

        if ( $date !== null && is_array( $date ) ) {
            $announcements = erp_hr_filter_collection_by_date( $announcements, $date, 'post_date' );
        }
        $announcements = $announcements->where( 'user_id', '=', $this->id )
                                       ->orderby( $wpdb->posts . '.post_date', 'desc' )
                                       ->skip( $offset )
                                       ->take( $limit );
        if ( $return_array ) {
            $announcements = $announcements->get()->toArray();
        } else {
            $announcements = $announcements->get();
        }

        return erp_array_to_object( $announcements );
    }

    /**
     * Get leave policies
     *
     * @since 1.3.0
     * @return mixed
     */
    public function get_leave_policies() {
        $financial_year_dates = erp_get_financial_year_dates();
        $entitlements         = $this->erp_user
            ->entitlements()
            ->whereDate( 'from_date', '>=', $financial_year_dates['start'] )
            ->whereDate( 'to_date', '<=', $financial_year_dates['end'] )
            ->JoinWithPolicy()
            ->orderBy( 'created_on', 'DESC' )
            ->select( array( 'days', 'policy_id', 'from_date', 'to_date', 'color', 'name' ) )
            ->get();

        return $entitlements;
    }

    /**
     * Get assigned entitlements
     *
     * @since 1.3.0
     *
     * @return array
     */
    public function get_entitlements( $args = array() ) {
        $financial_year_dates = erp_get_financial_year_dates();
        $defaults             = array(
            'policy_id' => 0,
            'from_date' => $financial_year_dates['start'],
            'to_date'   => $financial_year_dates['end'],
            'number'    => 20,
            'offset'    => 0,
            'orderby'   => 'created_on',
            'order'     => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        $entitlements = $this->erp_user->entitlements();
        if ( ! empty( $args['policy_id'] ) ) {
            $entitlements = $entitlements->where( 'policy_id', intval( $args['policy_id'] ) );
        }
        $entitlements = $entitlements->whereDate( 'from_date', '>=', $args['from_date'] )
                                     ->whereDate( 'to_date', '<=', $args['to_date'] )
                                     ->JoinWithPolicy()
                                     ->skip( $args['offset'] )
                                     ->take( $args['number'] )
                                     ->orderBy( $args['orderby'], $args['order'] )
                                     ->select( array( 'days', 'policy_id', 'from_date', 'to_date', 'color', 'name' ) )
                                     ->get();

        return $entitlements;
    }


    /**
     * Get leave balances of the current year
     *
     * @since 1.3.0
     *
     * @param null $date
     * @param null $policy_id
     *
     * @return array
     */
    public function get_leave_summary( $date = null, $policy_id = null ) {
        $date       = $date == null ? current_time( 'mysql' ) : $date;
        $year_dates = erp_get_financial_year_dates( $date );

        $balances = [];
        $start    = isset( $year_dates['start'] ) ? $year_dates['start'] : null;
        $end      = isset( $year_dates['end'] ) ? $year_dates['end'] : null;
        $user_id  = $this->user_id;

        $results = $this->erp_user
            ->entitlements()
            ->whereDate( 'from_date', '>=', $start )
            ->whereDate( 'to_date', '<=', $end )
            ->with( [
                'leaves' => function ( $q ) use ( $user_id, $start, $end ) {
                    $q->where( 'status', '=', '1' )
                      ->where( 'user_id', $user_id )
                      ->whereDate( 'start_date', '>=', $start )
                      ->whereDate( 'end_date', '<=', $end );
                }
            ] )
            ->with( 'policy' )
//            ->where('policy_id', '1')
            ->get();

        foreach ( $results as $result ) {
            $balance      = array(
                'entitlement_id' => $result->id,
                'days'           => intval( $result->days ),
                'from_date'      => $result->from_date,
                'to_date'        => $result->to_date,
                'policy'         => isset( $result->policy ) ? $result->policy->name : '',
                'policy_id'      => isset( $result->policy ) ? $result->policy->id : '',
            );
            $spent        = 0;
            $scheduled    = 0;
            $available    = $result->days;
            $current_time = current_time( 'timestamp' );
            foreach ( $result->leaves as $leave ) {
                $spent     += $leave->days;
                $available = $available - $leave->days;
                if ( $current_time < strtotime( $leave->start_date ) ) {
                    $scheduled += $leave->days;
                }
            }
            $balance['spent']     = $spent;
            $balance['scheduled'] = $scheduled;
            $balance['available'] = $available;

            $balances[] = $balance;

        }

        return erp_array_to_object( $balances );
    }

    /**
     * Get leave requests
     *
     * @since 1.3.0
     *
     * @param array $args
     *
     * @return mixed
     */
    public function get_leave_requests( $args = array() ) {
        $default = array(
            'year'      => date( 'Y' ),
            'status'    => 1,
            'orderby'   => 'start_date',
            'policy_id' => null,
            'number'    => 0,
            'offset'    => 0,
        );
        $args    = wp_parse_args( $args, $default );

        $requests = $this->get_erp_user()
                         ->leave_requests();
        if ( ! empty( $args['year'] ) ) {
            $requests = $requests->whereYear( 'start_date', '=', intval( $args['year'] ) );
        }
        if ( ! empty( $args['policy_id'] ) ) {
            $requests = $requests->where( 'policy_id', intval( $args['policy_id'] ) );
        }
        if ( ! empty( $args['status'] ) ) {
            $requests = $requests->where( 'status', intval( $args['status'] ) );
        }
        if ( ! empty( $args['offset'] ) ) {
            $requests = $requests->skip( intval( $args['offset'] ) );
        }
        if ( ! empty( $args['number'] ) ) {
            $requests = $requests->skip( intval( $args['number'] ) );
        }

        return $requests->JoinWithPolicy()->orderBy( 'start_date' )->select( [
            'start_date',
            'end_date',
            'reason',
            'days',
            'name'
        ] )->get();
    }

    /**
     * Get all the events of a single user
     *
     * @since 1.3.0
     *
     * @param array $date_range
     *
     * @return array
     *
     */
    public function get_calender_events( array $date_range = array() ) {
        global $wpdb;
        if ( empty( $date_range ) || empty( $date_range['start'] ) || empty( $date_range['end'] ) ) {
            $date_range = [
                'start' => date( "Y-01-01" ),
                'end'   => date( "Y-12-31" ),
            ];
        }
        $leave_requests = $this->get_erp_user()
                               ->leave_requests()
                               ->whereDate( 'start_date', '>=', $date_range['start'] )
                               ->whereDate( 'end_date', '<=', $date_range['end'] )->JoinWithPolicy()->orderBy( 'start_date' )
                               ->select( [
                                   "{$wpdb->prefix}erp_hr_leave_requests.id",
                                   'start_date',
                                   'end_date',
                                   'color',
                                   'status',
                                   'days',
                                   'name'
                               ] )
                               ->get();
        $holidays       = Leave_Holiday::whereDate( 'start', '>=', $date_range['start'] )
                                       ->whereDate( 'end', '<=', $date_range['end'] )
                                       ->get();

        $events = [];

        foreach ( $leave_requests as $leave_request ) {

            $request_rejected   =   3;

            if ( $leave_request->status == $request_rejected ) {
                continue;
            }

            $title = $leave_request->name;
            if ( $leave_request->status == 2 ) {
                $title .= sprintf( ' ( %s ) ', __( 'Pending', 'erp' ) );
            }
            $event = [
                'id'      => $leave_request->id,
                'start'   => date( 'Y-m-d', strtotime( $leave_request->start_date ) ),
                'end'     => date( 'Y-m-d', strtotime( $leave_request->end_date ) ),
                'color'   => $leave_request->color,
                'title'   => $title,
                'img'     => '',
                'holiday' => false,
            ];

            $events[] = $event;
        }

        foreach ( $holidays as $holiday ) {
            $event = [
                'id'      => $holiday->id,
                'start'   => date( 'Y-m-d', strtotime( $holiday->start ) ),
                'end'     => date( 'Y-m-d', strtotime( $holiday->end ) ),
                'color'   => '#FF5354',
                'title'   => $holiday->title,
                'img'     => '',
                'holiday' => true,
            ];

            $events[] = $event;
        }


        return $events;
    }

    /**
     * Get employee roles and caps
     *
     * @since 1.3.0
     *
     * @param bool $include_erp_only
     *
     * @return array
     */
    public function get_roles( $include_erp_only = true ) {
        $wp_user    = new \WP_User( $this->id );
        $user_roles = isset( $wp_user->roles ) ? $wp_user->roles : [];
        $all_caps   = isset( $wp_user->allcaps ) ? $wp_user->allcaps : [];
        $roles      = erp_get_editable_roles();
        if ( $include_erp_only ) {
            $roles = array();
            if ( function_exists( 'erp_hr_get_roles' ) ) {
                $roles = array_merge( $roles, erp_hr_get_roles() );
            }
            if ( function_exists( 'erp_crm_get_roles' ) ) {
                $roles = array_merge( $roles, erp_crm_get_roles() );
            }
            if ( function_exists( 'erp_ac_get_roles' ) ) {
                $roles = array_merge( $roles, erp_ac_get_roles() );
            }
        }
        $available_roles = [];
        foreach ( $roles as $key => $role ) {
            $available_roles[ $key ] = $role['name'];
        }
        $result = [
            'roles'           => $user_roles,
            'caps'            => $all_caps,
            'available_roles' => $available_roles
        ];

        return $result;
    }

    /**
     * Update employee roles
     * accepts associative array eg. ['erp_hr_manager' => true, 'erp_crm_manager' => false ]
     *
     * @since 1.3.0
     *
     * @param array $roles
     *
     * @return $this
     */
    public function update_role( $roles = [] ) {
        $erp_roles       = $this->get_roles();
        $available_roles = array_keys( $erp_roles['available_roles'] );
        $wp_user         = new \WP_User( $this->id );
        foreach ( $roles as $role => $boolean ) {
            if ( ! in_array( $role, $available_roles ) ) {
                continue;
            }
            $add_roles = filter_var( $boolean, FILTER_VALIDATE_BOOLEAN );

            if ( $add_roles ) {
                $wp_user->add_role( $role );
            } else {
                $wp_user->remove_role( $role );
            }
        }

        return $this;
    }

    /**
     * Terminate Employee
     *
     * @since 1.3.0
     *
     * @param array $args
     *
     * @return $this|\WP_Error
     */
    public function terminate( $args = array() ) {

        if ( ! $args['terminate_date'] ) {
            return new \WP_Error( 'no-date', 'Termination date is required' );
        }

        if ( ! $args['termination_type'] ) {
            return new \WP_Error( 'no-type', 'Termination type is required' );
        }

        if ( ! $args['termination_reason'] ) {
            return new \WP_Error( 'no-reason', 'Termination reason is required' );
        }

        if ( ! $args['eligible_for_rehire'] ) {
            return new \WP_Error( 'no-eligible-for-rehire', 'Eligible for rehire field is required' );
        }
        $this->erp_user->update( [
            'status'           => 'terminated',
            'termination_date' => $args['terminate_date']
        ] );

        $comments = sprintf( '%s: %s; %s: %s; %s: %s',
            __( 'Termination Type', 'erp' ),
            erp_hr_get_terminate_type( $args['termination_type'] ),
            __( 'Termination Reason', 'erp' ),
            erp_hr_get_terminate_reason( $args['termination_reason'] ),
            __( 'Eligible for Hire', 'erp' ),
            erp_hr_get_terminate_rehire_options( $args['eligible_for_rehire'] ) );

        $this->update_employment_status( [
            'status'   => 'terminated',
            'comments' => $comments,
            'date'     => $args['terminate_date'],
        ] );

        update_user_meta( $this->id, '_erp_hr_termination', $args );

        return $this;
    }

    /**
     * get restricted data
     *
     * @since 1.3.0
     * @return array
     */
    protected function get_restricted_employee_data() {
        $restricted_data = array();

        return apply_filters( 'erp_hr_employee_restricted_data', $restricted_data, $this->user_id, $this );
    }

    /**
     * Send error
     *
     * @since 1.3.0
     *
     * @param $code
     * @param $message
     *
     * @return \WP_Error
     */
    protected function send_error( $code, $message ) {
        return new \WP_Error( $code, $message );
    }


}
