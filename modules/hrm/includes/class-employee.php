<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Admin\Models\Company_Locations;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\HRM\Models\Employee_History;
use WeDevs\ERP\HRM\Models\Hr_User;

class Employee {

    /**
     * user id
     *
     * @var int
     */
    public $user_id;
    /**
     * wp user
     *
     * @type \WP_User
     * @var \stdClass
     */
    protected $wp_user;

    /**
     * Model Instance
     *
     * @type \WeDevs\ERP\HRM\Models\Employee
     * @var
     */
    protected $erp_user;

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
        )
    );

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
        $this->user_id  = 0;
        $this->wp_user  = new \WP_User();
        $this->erp_user = new \WeDevs\ERP\HRM\Models\Employee();
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

        if ( is_int( $employee ) ) {

            $user = get_user_by( 'id', $employee );

            if ( $user ) {
                $this->user_id = $employee;
                $this->wp_user = $user;
            }

        } elseif ( is_a( $employee, 'WP_User' ) ) {

            $this->user_id = $employee->id;
            $this->wp_user = $employee;

        } elseif ( is_email( $employee ) ) {

            $user = get_user_by( 'email', $employee );

            if ( $user ) {
                $this->user_id = $employee;
                $this->wp_user = $user;
            }

        }

        if ( $this->user_id ) {
            $this->erp_user = \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $this->user_id )->first();
            if ( $this->is_employee() ) {
                $this->data['user_id']    = $this->user_id;
                $this->data['user_email'] = $this->wp_user->user_email;

                foreach ( $this->data['work'] as $key => $value ) {
                    $this->data['work'][ $key ] = $this->$key;
                }

                foreach ( $this->data['personal'] as $key => $value ) {
                    $this->data['personal'][ $key ] = $this->$key;
                }
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

        //if user found by id then update the user
        if ( ! empty( $data['user_id'] ) ) {
            if ( get_user_by( 'ID', absint( $data['user_id'] ) ) ) {
                return $this->update_employee( $data );
            }
        }

        //if user found by email then update the user
        if ( ! empty( $data['user_email'] ) ) {
            if ( get_user_by( 'email', absint( $data['user_email'] ) ) ) {
                return $this->update_employee( $data );
            }
        }

        $data['user_email'] = strtolower( $data['user_email'] );

        if ( empty( $data['personal']['first_name'] ) ) {
            return new \WP_Error( 'empty-first-name', __( 'Please provide the first name.', 'erp' ) );
        }
        if ( empty( $data['personal']['last_name'] ) ) {
            return new \WP_Error( 'empty-last-name', __( 'Please provide the last name.', 'erp' ) );
        }
        if ( ! is_email( $data['user_email'] ) ) {
            return new \WP_Error( 'invalid-email', __( 'Please provide a valid email address.', 'erp' ) );
        }

        $userdata = array(
            'user_login'   => $data['user_email'],
            'user_email'   => $data['user_email'],
            'first_name'   => $data['personal']['first_name'],
            'last_name'    => $data['personal']['last_name'],
            'user_url'     => $data['personal']['user_url'],
            'display_name' => $data['personal']['first_name'] . ' ' . $data['personal']['middle_name'] . ' ' . $data['personal']['last_name'],
            'user_pass'    => wp_generate_password( 12 ),
            'role'         => 'employee',
        );

        $userdata = apply_filters( 'erp_hr_employee_args', $userdata );
        $user_id  = wp_insert_user( $userdata );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        // if reached here, seems like we have success creating the user
        $this->load_employee( $user_id );

        // inserting the user for the first time
        $hiring_date = ! empty( $data['work']['hiring_date'] ) ? $data['work']['hiring_date'] : current_time( 'mysql' );

        $work = $data['work'];

        if ( ! empty( $work['type'] ) ) {
            $this->update_employment_status( $work['type'], $hiring_date );
        }

        // update compensation
        if ( ! empty( $work['pay_rate'] ) ) {
            $pay_type = ( ! empty( $work['pay_type'] ) ) ? $work['pay_type'] : 'monthly';
            $this->update_compensation( $work['pay_rate'], $pay_type, '', $hiring_date );
        }

        // update job info
        $this->update_job_info( $work['department'], $work['designation'], $work['reporting_to'], $work['location'], $hiring_date );

        $this->update_employee( array_merge( $data['work'], $data['personal'] ) );

        do_action( 'erp_hr_employee_new', $this->id, $data );
        return $this;
    }

    /**
     * Update employee
     *
     * @since 1.2.9
     *
     * @param array $data
     *
     * @return $this
     */
    public function update_employee( $data = array() ) {
        $restricted = [
            'user_id',
            'user_email',
            'user_url',
            'id',
            'ID',
        ];

        $posted = array_map( 'strip_tags_deep', $data );
        $posted = erp_array_flatten( $posted );
        $posted = array_except( $posted, $restricted );

        foreach ( $posted as $key => $value ) {
            if ( ! empty( $value ) && ( $this->$key != $value ) ) {
                if ( array_key_exists( $key, $this->data['work'] ) ) {
                    $this->changes['work'][ $key ] = $value;
                } else {
                    $this->changes['personal'][ $key ] = $value;
                }
            }
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
     * @since 1.2.9
     *
     * @param array $data
     *
     * @return array|void|mixed|string
     */
    function get_data( $data = array() ) {
        $employee_data = array_merge( $data, $this->data );
        return apply_filters( 'erp_hr_get_employee_fields', $employee_data, $this->user_id, $this->wp_user );
    }

    /**
     *  Get the user info as an array
     *
     * @deprecated 1.2.9
     * @return array|mixed|string|void
     */
    public function to_array() {
        $data['user_id']         = $this->user_id;
        $data['employee_id']     = $this->employee_id;
        $data['user_email']      = $this->user_email;
        $data['name']            = array(
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'middle_name' => $this->middle_name,
            'full_name'   => $this->get_full_name()
        );
        $avatar_id               = $this->avatar_id;
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
     * @since 1.2.9
     * @return bool
     */
    public function is_employee() {
        if ( $this->erp_user ) {
            return true;
        }

        return false;
    }

    public function get_id() {
        return $this->user_id;
    }

    public function get_photo_id() {
        if ( isset( $this->user->photo_id ) ) {
            return (int) $this->user->photo_id;
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
        if ( $this->user_id && ! empty( $this->photo_id ) ) {
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
        if ( $this->user_id && ! empty( $this->photo_id ) ) {
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
            return admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $this->user_id );
        }
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
     * Get the job title
     *
     * @return string
     */
    public function get_job_title() {
        if ( $this->user_id && $this->designation ) {
            $designation = Designation::find( $this->designation );
            if ( $designation ) {
                return stripslashes( $designation->title );
            }
        }
        return null;
    }

    /**
     * Get the department title
     *
     * @return string
     */
    public function get_department_title() {
        if ( $this->id && $this->department ) {
            $department = Department::find( $this->department );
            if ( $department ) {
                return stripslashes( $department->title );
            }
        }

        return null;
    }

    /**
     * Get the work location title
     *
     * @return string
     */
    public function get_work_location() {
        if ( $this->user_id && $this->erp_user->location ) {
            $location = Company_Locations::find( $this->location );
            if ( $location ) {
                return stripslashes( $location->name );
            }
        }

        return null;
    }

    /**
     * Get the work location id
     *
     * @since 1.2.0
     *
     * @return int
     */
    public function get_location() {
        if ( $this->erp_user->location ) {
            return $this->erp_user->location;
        }
    }

    /**
     * Get the employee status
     *
     * @return string
     */
    public function get_status() {
        if ( $this->erp_user->status ) {
            $statuses = erp_hr_get_employee_statuses();

            if ( array_key_exists( $this->erp_user->status, $statuses ) ) {
                return $statuses[ $this->erp_user->status ];
            }
        }
    }

    /**
     * Get the employee type
     *
     * @return string
     */
    public function get_type() {
        if ( $this->erp_user->type ) {
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
    public function get_hiring_source() {
        if ( ! empty( $this->erp_user->hiring_source ) ) {
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
    public function get_gender() {
        if ( ! empty( $this->user->gender ) ) {
            $genders = erp_hr_get_genders();

            if ( array_key_exists( $this->user->gender, $genders ) ) {
                return $genders[ $this->user->gender ];
            }
        }
    }

    /**
     * Get the marital status
     *
     * @return string
     */
    public function get_marital_status() {
        if ( ! empty( $this->user->marital_status ) ) {
            $statuses = erp_hr_get_marital_statuses();

            if ( array_key_exists( $this->user->marital_status, $statuses ) ) {
                return $statuses[ $this->user->marital_status ];
            }
        }
    }


    /**
     * Get the employee nationalit
     *
     * @return string
     */
    public function get_nationality() {
        if ( ! empty( $this->user->nationality ) ) {
            $countries = \WeDevs\ERP\Countries::instance()->get_countries();

            if ( array_key_exists( $this->user->nationality, $countries ) ) {
                return $countries[ $this->user->nationality ];
            }
        }
    }

    /**
     * Get joined date
     *
     * @return string
     */
    public function get_joined_date() {
        if ( $this->erp_user->hiring_date != '0000-00-00' ) {
            return erp_format_date( $this->erp_user->hiring_date );
        }
    }

    public function get_date_of_birth() {
        $date = '';
        if ( isset( $this->erp_user->date_of_birth ) && ( $this->erp_user->date_of_birth != '0000-00-00' ) ) {
            $date = erp_format_date( $this->erp_user->date_of_birth );
        }
        return $date;
    }

    public function set_date_of_birth( $date ) {
        if ( ! empty( $date ) ) {
            $this->changes['work']['date_of_birth'] = date( 'Y-m-d H:i:s', strtotime( $date ) );
        }
    }

    /**
     * Get Address 1
     *
     * @return string
     */
    public function get_street_1() {
        if ( ! empty( $this->erp_user->street_1 ) ) {
            return $this->erp_user->street_1;
        }
    }

    /**
     * Get Address 2
     *
     * @return string
     */
    public function get_street_2() {
        if ( ! empty( $this->erp_user->street_2 ) ) {
            return $this->erp_user->street_2;
        }
    }

    /**
     * Get City
     *
     * @return string
     */
    public function get_city() {
        if ( ! empty( $this->erp_user->city ) ) {
            return $this->erp_user->city;
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
    public function get_country() {
        return erp_get_country_name( $this->wp_user->country );
    }


    /**
     * Get State
     *
     * @return string
     */
    public function get_state() {
        return erp_get_state_name( $this->wp_user->country, $this->wp_user->state );
    }

    /**
     * Get the name of reporting user
     *
     * @return string
     */
    public function get_reporting_to() {
        if ( $this->erp_user->reporting_to ) {
            $user_id = (int) $this->erp_user->reporting_to;
            $user    = new Employee( $user_id );

            if ( $user->user_id ) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Get a phone number
     *
     * @param  string  type of phone. work|mobile|phone
     *
     * @return string
     */
    public function get_phone( $which = 'work' ) {
        $phone = '';

        switch ( $which ) {
            case 'mobile':
                $phone = isset( $this->user->mobile ) ? $this->user->mobile : '';
                break;

            case 'phone':
                $phone = isset( $this->user->phone ) ? $this->user->phone : '';
                break;

            default:
                $phone = isset( $this->user->work_phone ) ? $this->user->work_phone : '';
                break;
        }

        return $phone;
    }

    /**
     * Get birth date
     *
     * @deprecated 1.2.9
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

    public function add_education( $data ) {
    }

    public function delete_education( $id ) {
    }

    /**
     * Get dependents
     *
     * @return array the dependents
     */
    public function get_dependants( $limit = 30, $offset = 0 ) {
        return $this->erp_user
            ->dependents()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }


    public function add_dependants( $data ) {
    }

    public function delete_dependants( $id ) {
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
     * Get job histories
     *
     * @return array
     */
    public function get_histories( $module = 'all', $limit = 30, $offset = 0 ) {
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
            $parsed_history = erp_hr_translate_employee_history( $history->toArray() );
            if ( $parsed_history ) {
                $formatted_histories[] = $parsed_history;
            }
        }

        return $formatted_histories;
    }


    public function add_history( $data ) {
        $modules = erp_hr_employee_history_modules();

        $module = empty( $history['module'] ) ? '' : $history['module'];
        if ( ! in_array( $module, $modules ) ) {
            return new \WP_Error( 'invalid-module-type', __( 'Invalid module or module does not exist', 'erp' ) );
        }

        //update employee data
        $update = [];
        foreach ( $history as $key => $val ) {
            if ( array_key_exists( $key, $this->data['work'] ) ) {
                $update[ $key ] = $val;
            }
        }
        $this->update_employee( $update );

        if ( ! empty( $history['designation'] ) ) {
            $history['designation'] = $this->get_job_title();
        }

        if ( ! empty( $history['department'] ) ) {
            $history['department'] = $this->get_department_title();
        }

        if ( ! empty( $history['location'] ) ) {
            $history['location'] = $this->get_work_location();
        }

        //prepare for inserting history
        $employee_history = erp_hr_translate_employee_history( apply_filters( 'erp_update_employee_history_data', $history, $this->id ), true );


        $parsed_history = wp_parse_args( [
            'user_id' => $this->id,
            'date'    => current_time( 'mysql' )
        ], $employee_history );

        $created = Employee_History::updateOrCreate( $parsed_history );
        if ( ! $created ) {
            return new \WP_Error( 'employee-history-update-failed', __( 'Employee history updating failed', 'erp' ) );
        }
        $created_parsed_history = erp_hr_translate_employee_history( $created->toArray() );

        return $created_parsed_history;
    }

    public function delete_history( $id ) {

    }

    /**
     * Update employment status
     *
     * @param        $new_status
     * @param string $date
     * @param string $comment
     *
     * @return array|bool|\WP_Error
     */
    public function update_employment_status( $new_status, $date = '', $comment = '' ) {

        return $this->add_history( [
            'date'    => $date,
            'type'    => $new_status,
            'comment' => $comment,
            'module'  => 'employment',
        ] );
    }

    /**
     * Update compensation of the employee
     *
     * @param int    $rate
     * @param string $type
     * @param string $reason
     * @param string $date
     * @param string $comment
     *
     * @return array|bool|\WP_Error
     */
    public function update_compensation( $rate = 0, $type = '', $reason = '', $date = '', $comment = '' ) {
        return $this->add_history( [
            'date'     => $date,
            'comment'  => $comment,
            'pay_type' => $type,
            'pay_rate' => $rate,
            'reason'   => $reason,
            'module'   => 'compensation',
        ] );
    }

    /**
     * update job info
     *
     * @param        $department_id
     * @param        $designation_id
     * @param int    $reporting_to
     * @param int    $location
     * @param string $date
     *
     * @return array|bool|\WP_Error
     */
    public function update_job_info( $department_id, $designation_id, $reporting_to = 0, $location = 0, $date = '' ) {
        return $this->add_history( [
            'date'         => $date,
            'designation'  => $designation_id,
            'department'   => $department_id,
            'reporting_to' => $reporting_to,
            'location'     => $location,
            'module'       => 'job',
        ] );
    }

    /**
     * Get employee performance list
     *
     * @param string $type
     * @param int    $limit
     * @param int    $offset
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
                                     ->get();

        return $performances;
    }

    public function add_performance( $data ) {
    }

    public function delete_performance( $id ) {
    }

    /**
     * Get employee performance list
     *
     * @param int $limit
     * @param int $offset
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function get_dependents( $limit = 30, $offset = 0 ) {

        $dependants = $this->erp_user
            ->dependents()
            ->skip( $offset )
            ->take( $limit )
            ->get();

        return $dependants;
    }

    public function add_dependents( $data ) {
    }

    public function delete_dependents( $id ) {
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


    public function add_note( $note, $comment_by = null, $return_object = false ) {
        global $wpdb;

        if ( $comment_by == null ) {
            $comment_by = get_current_user_id();
        }

        $data = array(
            'user_id'    => $this->id,
            'comment'    => $note,
            'comment_by' => $comment_by,
        );

        $note = \WeDevs\ERP\HRM\Models\Employee_Note::create( $data );

        if ( $note->id ) {
            $note_id = $note->id;
            do_action( 'erp_hr_employee_note_new', $note_id, $this->id );

            if ( $return_object ) {
                return $note;
            }

            return $note_id;
        }

        return false;
    }


    public function delete_note( $id ) {
    }

    /**
     * Get announcements
     *
     * @since 1.2.9
     *
     * @param null $date
     * @param bool $return_array
     * @param int  $limit
     * @param int  $offset
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
     * Get assigned entitlements
     *
     * @since 1.2.9
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

        $entitlements = $this->employee->entitlements();
        if ( ! empty( $args['policy_id'] ) ) {
            $entitlements = $entitlements->where( 'policy_id', intval( $args['policy_id'] ) );
        }
        $entitlements = $entitlements->where( 'from_date', $args['from_date'] )
                                     ->where( 'to_date', $args['to_date'] )
                                     ->skip( $args['offset'] )
                                     ->take( $args['number'] )
                                     ->orderBy( $args['orderby'], $args['order'] )
                                     ->get();

        return $entitlements;
    }

    /**
     * Get leave balances
     *
     * @since 1.2.9
     * @return bool|float
     */
    public function get_leave_balance() {
        return erp_hr_leave_get_balance( $this->id );
    }

    /**
     * Get employee roles and caps
     *
     * @since 1.2.9
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
            $roles = array_merge( erp_hr_get_roles(), erp_crm_get_roles(), erp_ac_get_roles() );
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
     * @since 1.2.9
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
     * @since 1.2.9
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
        $this->update_employee( [
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

        $this->add_history( [
            'module'  => 'employment',
            'date'    => $args['terminate_date'],
            'type'    => 'terminated',
            'comment' => $comments,
        ] );

        update_user_meta( $this->id, '_erp_hr_termination', $args );

        return $this;
    }

}