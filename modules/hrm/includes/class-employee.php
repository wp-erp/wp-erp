<?php

namespace WeDevs\ERP\HRM;

use Carbon\Carbon;
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
    protected $id;
    /**
     * wp user
     *
     * @type \WP_User
     * @var \stdClass
     */
    protected $user;

    /**
     * Model Instance
     *
     * @type \WeDevs\ERP\HRM\Models\Employee
     * @var
     */
    protected $employee;

    /**
     * Employee data
     *
     * @var array
     */
    protected $data = array(
        'user_email' => '',
        'work'       => array(
            'designation'   => 0,
            'department'    => 0,
            'location'      => '',
            'hiring_source' => '',
            'hiring_date'   => '',
            'date_of_birth' => '',
            'reporting_to'  => 0,
            'pay_rate'      => '',
            'pay_type'      => '',
            'type'          => '',
            'status'        => '',
        ),
        'personal'   => array(
            'photo_id'        => 0,
            'user_id'         => 0,
            'employee_id'     => '',
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
     * Employee constructor.
     *
     * @param null $employee
     */
    public function __construct( $employee = null ) {
        $this->id       = 0;
        $this->user     = new \stdClass();
        $this->employee = new \stdClass();
        if ( $employee !== null ) {
            $this->load_employee( $employee );
        }
    }

    /**
     * Load employee
     *
     * @since 1.2.9
     *
     * @param $employee
     */
    protected function load_employee( $employee ) {
        if ( is_int( $employee ) ) {

            $user = get_user_by( 'id', $employee );

            if ( $user ) {
                $this->id   = $employee;
                $this->user = $user;
            }

        } elseif ( is_a( $employee, 'WP_User' ) ) {

            $this->id   = $employee->id;
            $this->user = $employee;

        } elseif ( is_email( $employee ) ) {

            $user = get_user_by( 'email', $employee );

            if ( $user ) {
                $this->id   = $employee;
                $this->user = $user;
            }

        }
        if ( $this->id ) {
            $this->employee = \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $this->id )->first();
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

        if ( isset( $this->$key ) ) {
            return stripslashes( $this->$key );
        }
        if ( isset( $this->employee->$key ) ) {
            return stripslashes( $this->employee->$key );
        }

        if ( isset( $this->user->$key ) ) {
            return stripslashes( $this->user->$key );
        }

        if ( method_exists( $this->employee, $key ) ) {
            return $this->employee->$key();
        }
    }

    /**
     * Create employee
     *
     * @since 1.2.9
     *
     * @param array $args
     *
     * @return $this|\WP_Error
     */
    public function create_employee( $args = array() ) {
        global $wpdb;
        $posted = array_map( 'strip_tags_deep', $args );
        $posted = array_map( 'trim_deep', $posted );
        $data   = erp_parse_args_recursive( $posted, $this->data );

        //change email to lowercase
        $data['user_email'] = strtolower( $data['user_email'] );

        // some basic validation
        if ( empty( $data['personal']['first_name'] ) ) {
            return new \WP_Error( 'empty-first-name', __( 'Please provide the first name.', 'erp' ) );
        }

        if ( empty( $data['personal']['last_name'] ) ) {
            return new \WP_Error( 'empty-last-name', __( 'Please provide the last name.', 'erp' ) );
        }

        if ( ! is_email( $data['user_email'] ) ) {
            return new \WP_Error( 'invalid-email', __( 'Please provide a valid email address.', 'erp' ) );
        }


        // attempt to create the user
        $userdata = array(
            'user_login'   => $data['user_email'],
            'user_email'   => $data['user_email'],
            'first_name'   => $data['personal']['first_name'],
            'last_name'    => $data['personal']['last_name'],
            'user_url'     => $data['personal']['user_url'],
            'display_name' => $data['personal']['first_name'] . ' ' . $data['personal']['middle_name'] . ' ' . $data['personal']['last_name'],
        );
        // if user id exists, do an update
        $user_id = isset( $posted['user_id'] ) ? intval( $posted['user_id'] ) : 0;
        $update  = false;
        if ( $user_id ) {
            $update         = true;
            $userdata['ID'] = $user_id;

        } else {
            // when creating a new user, assign role and passwords
            $userdata['user_pass'] = wp_generate_password( 12 );
            $userdata['role']      = 'employee';
        }
        $userdata = apply_filters( 'erp_hr_employee_args', $userdata );
        $wp_user  = get_user_by( 'email', $userdata['user_login'] );

        /**
         * We hook `erp_hr_existing_role_to_employee` to the `set_user_role` action
         * in action-fiters.php file. Since we have set `$userdata['role'] = 'employee'`
         * after insert/update a wp user, `erp_hr_existing_role_to_employee` function will
         * create an employee immediately
         */
        if ( $wp_user ) {
            unset( $userdata['user_url'] );
            unset( $userdata['user_pass'] );
            $userdata['ID'] = $wp_user->ID;

            $user_id = wp_update_user( $userdata );

        } else {
            $user_id = wp_insert_user( $userdata );
        }
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        // if reached here, seems like we have success creating the user
        $this->load_employee( $user_id );

        // inserting the user for the first time
        $hiring_date = ! empty( $data['work']['hiring_date'] ) ? $data['work']['hiring_date'] : current_time( 'mysql' );
        if ( ! $update ) {

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
        }


        $employee_table_data = array(
            'hiring_source' => $data['work']['hiring_source'],
            'hiring_date'   => $hiring_date,
            'date_of_birth' => $data['work']['date_of_birth'],
            'employee_id'   => $data['personal']['employee_id']
        );

        // employees should not be able to change hiring date, unset when their profile
        if ( $update && ! current_user_can( erp_hr_get_manager_role() ) ) {
            unset( $employee_table_data['hiring_date'] );
        }

        if ( ! $update ) {
            $employee_table_data['status'] = $data['work']['status'];
        }

        // update the erp table
        $wpdb->update( $wpdb->prefix . 'erp_hr_employees', $employee_table_data, array( 'user_id' => $user_id ) );

        foreach ( $data['personal'] as $key => $value ) {
            if ( in_array( $key, [ 'employee_id', 'user_url' ] ) ) {
                continue;
            }

            update_user_meta( $user_id, $key, $value );
        }

        if ( $update ) {
            do_action( 'erp_hr_employee_update', $user_id, $data );
        } else {
            do_action( 'erp_hr_employee_new', $user_id, $data );
        }

        return $this;
    }

    /**
     * Update employee data
     *
     * @param array $data
     *
     * @return $this
     */
    public function update( $data = array() ) {
        $fill_able = $this->employee->getFillable();
        $update_able = array();
        foreach ( $data as $key => $val ){
            if( in_array($key, $fill_able)){
                $update_able[$key] = $val;
            }
        }
        if( !empty($updatable)){
            $this->employee->update( $data );
        }
        return $this;
    }

    /**
     * Get the user info as an array
     *
     * @return array
     */
    public function to_array() {
        if ( $this->id ) {
            $this->data['id']          = $this->id;
            $this->data['employee_id'] = $this->employee_id;
            $this->data['user_email']  = $this->user->user_email;

            $this->data['name'] = array(
                'first_name'  => $this->first_name,
                'last_name'   => $this->last_name,
                'middle_name' => $this->middle_name,
                'full_name'   => $this->get_full_name()
            );

            $avatar_id                     = (int) $this->user->photo_id;
            $this->data['avatar']['id']    = $avatar_id;
            $this->data['avatar']['image'] = $this->get_avatar();

            if ( $avatar_id ) {
                $this->data['avatar']['url'] = $this->get_avatar_url( $avatar_id );
            }

            foreach ( $this->data['work'] as $key => $value ) {
                $this->data['work'][ $key ] = $this->$key;
            }

            foreach ( $this->data['personal'] as $key => $value ) {
                $this->data['personal'][ $key ] = $this->user->$key;
            }
        }

        return apply_filters( 'erp_hr_get_employee_fields', $this->data, $this->id, $this->user );
    }

    /**
     * Get an employee avatar
     *
     * @param  integer  avatar size in pixels
     *
     * @return string   image with HTML tag
     */
    public function get_avatar_url( $size = 32 ) {
        if ( $this->id && ! empty( $this->user->photo_id ) ) {
            return wp_get_attachment_url( $this->user->photo_id );
        }

        return get_avatar_url( $this->id, [ 'size' => $size ] );
    }

    /**
     * Get an employee avatar url
     *
     * @param  integer  avatar size in pixels
     *
     * @return string   image with HTML tag
     */
    public function get_avatar( $size = 32 ) {
        if ( $this->id && ! empty( $this->user->photo_id ) ) {
            $image = wp_get_attachment_thumb_url( $this->user->photo_id );

            return sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
        }

        $avatar = get_avatar( $this->id, $size );

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
        if ( $this->id ) {
            return admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $this->id );
        }
    }

    /**
     * Get the employees full name
     *
     * @return string
     */
    public function get_full_name() {
        $name = array();

        if ( ! empty( $this->user->first_name ) ) {
            $name[] = $this->user->first_name;
        }

        if ( ! empty( $this->user->middle_name ) ) {
            $name[] = $this->user->middle_name;
        }

        if ( ! empty( $this->user->last_name ) ) {
            $name[] = $this->user->last_name;
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
        if ( $this->id && $this->designation ) {
            $designation = Designation::find( $this->designation );

            return stripslashes( $designation->title );
        }
    }

    /**
     * Get the department title
     *
     * @return string
     */
    public function get_department_title() {
        if ( $this->id && $this->department ) {
            $department = Department::find( $this->department );

            return stripslashes( $department->title );
        }
    }

    /**
     * Get the work location title
     *
     * @return string
     */
    public function get_work_location() {
        if ( $this->id && $this->location ) {
            $location = Company_Locations::find( $this->location );

            return stripslashes( $location->name );
        }
    }

    /**
     * Get the work location id
     *
     * @since 1.2.0
     *
     * @return int
     */
    public function get_work_location_id() {
        return $this->location;
    }

    /**
     * Get the employee status
     *
     * @return string
     */
    public function get_status() {
        if ( $this->status ) {
            $statuses = erp_hr_get_employee_statuses();

            if ( array_key_exists( $this->status, $statuses ) ) {
                return $statuses[ $this->status ];
            }
        }
    }


    /**
     * Get the employee type
     *
     * @return string
     */
    public function get_type() {
        if ( $this->type ) {
            $types = erp_hr_get_employee_types();

            if ( array_key_exists( $this->type, $types ) ) {
                return $types[ $this->type ];
            }
        }
    }

    /**
     * Get the employee work_hiring_source
     *
     * @return string
     */
    public function get_hiring_source() {
        if ( $this->hiring_source ) {
            $sources = erp_hr_get_employee_sources();

            if ( array_key_exists( $this->hiring_source, $sources ) ) {
                return $sources[ $this->hiring_source ];
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
        if ( $this->hiring_date != '0000-00-00' ) {
            return erp_format_date( $this->hiring_date );
        }
    }

    /**
     * Get birth date
     *
     * @return string
     */
    public function get_birthday() {
        if ( $this->date_of_birth != '0000-00-00' ) {
            return erp_format_date( $this->date_of_birth );
        }
    }

    /**
     * Get Address 1
     *
     * @return string
     */
    public function get_street_1() {
        return ( $this->street_1 ) ? $this->street_1 : '—';
    }

    /**
     * Get Address 2
     *
     * @return string
     */
    public function get_street_2() {
        return ( $this->street_2 ) ? $this->street_2 : '—';
    }

    /**
     * Get City
     *
     * @return string
     */
    public function get_city() {
        return ( $this->city ) ? $this->city : '—';
    }

    /**
     * Get Country
     *
     * @return string
     */
    public function get_country() {
        return erp_get_country_name( $this->country );
    }

    /**
     * Get State
     *
     * @return string
     */
    public function get_state() {
        return erp_get_state_name( $this->country, $this->state );
    }

    /**
     * Get Postal Code
     *
     * @return string
     */
    public function get_postal_code() {
        return ( $this->postal_code ) ? $this->postal_code : '—';
    }

    /**
     * Get the name of reporting user
     *
     * @return string
     */
    public function get_reporting_to() {
        if ( $this->reporting_to ) {
            $user_id = (int) $this->reporting_to;
            $user    = new Employee( $user_id );

            if ( $user->id ) {
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
     * Get educational qualifications
     *
     * @return array the qualifications
     */
    public function get_educations( $limit = 30, $offset = 0 ) {
        return $this->employee
            ->educations()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }

    /**
     * Get dependents
     *
     * @return array the dependents
     */
    public function get_dependants( $limit = 30, $offset = 0 ) {
        return $this->employee
            ->dependents()
            ->skip( $offset )
            ->take( $limit )
            ->get();
    }

    /**
     * Get work experiences
     *
     * @return array
     */
    public function get_experiences( $limit = 30, $offset = 0 ) {
        return $this->employee
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
        $histories = $this->employee->histories();
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

    /**
     * Create or update a history
     *
     * @since 1.2.9
     *
     * @param $history
     *
     * @return array|bool|\WP_Error
     */
    public function create_or_update_history( $history ) {
        $modules = erp_hr_employee_history_modules();

        $module = empty( $history['module'] ) ? '' : $history['module'];
        if ( ! in_array( $module, $modules ) ) {
            return new \WP_Error( 'invalid-module-type', __( 'Invalid module or module does not exist', 'erp' ) );
        }
        $accepted_base_fields = [
            'designation',
            'department',
            'location',
            'hiring_source',
            'hiring_date',
            'termination_data',
            'date_of_birth',
            'reporting_to',
            'pay_rate',
            'pay_type',
            'type',
            'status',
        ];

        $updatable_base_fields = array();

        foreach ( $history as $key => $val ) {
            if ( in_array( $key, $accepted_base_fields ) ) {
                $updatable_base_fields[ $key ] = $val;
            }
        }

        //update employee table
        $this->update( $updatable_base_fields );

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

        return $this->create_or_update_history( [
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
        return $this->create_or_update_history( [
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
        return $this->create_or_update_history( [
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

        $performances = $this->employee->performances();
        if ( ( $type !== 'all' ) && ( in_array( $type, $types ) ) ) {
            $performances = $performances->where( 'type', $type );
        }

        $performances = $performances->skip( $offset )
                                     ->take( $limit )
                                     ->get();

        return $performances;
    }

    /**
     * Add a new note
     *
     * @param string $note the note to be added
     * @param int    $comment_by
     * @$return_object boolean
     *
     * @return int|object note id
     */
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

    /**
     * Get all notes
     *
     * @param  int $limit
     * @param  int $offset
     *
     * @return array
     */
    public function get_notes( $limit = 30, $offset = 0 ) {

        return Hr_User::find( $this->id )
                      ->notes()
                      ->skip( $offset )
                      ->take( $limit )
                      ->get();
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

    public function get_entitlements( $date = null, $return_array = false, $limit = 30, $offset = 0 ) {
        return $this->employee->entitlements()->toSql();
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

        $this->update( [
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

        $this->create_or_update_history( [
            'module'  => 'employment',
            'date'    => $args['terminate_date'],
            'type'    => 'terminated',
            'comment' => $comments,
        ] );

        update_user_meta( $this->id, '_erp_hr_termination', $args );

        return $this;
    }


}
