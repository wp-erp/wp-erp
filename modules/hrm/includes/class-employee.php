<?php
namespace WeDevs\ERP\HRM;

/**
 * Employee Class
 */
class Employee {

    /**
     * [__construct description]
     *
     * @param int|WP_User|email user id or WP_User object or user email
     */
    public function __construct( $employee = null ) {

        $this->id   = 0;
        $this->user = new \stdClass();
        $this->erp  = new \stdClass();

        if ( is_int( $employee ) ) {

            $user = get_user_by( 'id', $employee );

            if ( $user ) {
                $this->id   = $employee;
                $this->user = $user;
                $this->erp  = $this->get_erp_row();
            }

        } elseif ( is_a( $employee, 'WP_User' ) ) {

            $this->id   = $employee->ID;
            $this->user = $employee;
            $this->erp  = $this->get_erp_row();

        } elseif ( is_email( $employee ) ) {

            $user = get_user_by( 'email', $employee );

            if ( $user ) {
                $this->id   = $employee;
                $this->user = $user;
                $this->erp  = $this->get_erp_row();
            }

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
        if ( isset( $this->user->$key ) ) {
            return stripslashes( $this->user->$key );
        }

        if ( isset( $this->erp->$key ) ) {
            return stripslashes( $this->erp->$key );
        }
    }

    /**
     * Get the user info as an array
     *
     * @return array
     */
    public function to_array() {
        $fields = array(
            'id'              => 0,
            'employee_id'     => '',
            'name'            => array(
                'first_name'      => '',
                'middle_name'     => '',
                'last_name'       => ''
            ),
            'avatar'          => array(
                'id'  => 0,
                'url' => ''
            ),
            'user_email'      => '',
            'work'            => array(
                'company_id'        => '',
                'department'        => '',
                'department_title'  => '',
                'designation'       => '',
                'designation_title' => '',
                'reporting_to'      => '',
                'joined'            => '',
                'hiring_source'     => '',
                'status'            => '',
                'type'              => '',
                'phone'             => '',
            ),
            'personal'        => array(
                'phone'           => '',
                'mobile'          => '',
                'address'         => '',
                'other_email'     => '',
                'dob'             => '',
                'gender'          => '',
                'nationality'     => '',
                'marital_status'  => '',
                'driving_license' => '',
                'hobbies'         => '',
                'user_url'        => '',
            )
        );

        $erp_fields = array(
            'company_id',
            'department',
            'department_title',
            'designation',
            'designation_title'
        );

        if ( $this->id ) {
            foreach ($fields as $key => $value) {
                if ( is_array( $value ) ) {

                    if ( $key == 'name' ) {

                        $fields['name'] = array(
                            'first_name'  => $this->first_name,
                            'last_name'   => $this->last_name,
                            'middle_name' => $this->middle_name,
                            'full_name'   => $this->get_full_name()
                        );
                        continue;

                    } elseif ( $key == 'avatar' ) {

                        $avatar_id                 = (int) $this->user->photo_id;
                        $fields['avatar']['id']    = $avatar_id;
                        $fields['avatar']['image'] = $this->get_avatar();

                        if ( $avatar_id ) {
                            $fields['avatar']['url'] = wp_get_attachment_url( $avatar_id );
                        }
                        continue;
                    }

                    foreach ($value as $n_key => $n_value) {

                        // if not erp_fields, fetch them as $key_$value
                        if ( in_array( $n_key, $erp_fields ) ) {
                            $fields[ $key ][ $n_key ] = $this->$n_key;
                        } else {
                            $meta_key                 = $key . '_' . $n_key;
                            $fields[ $key ][ $n_key ] = $this->$meta_key;
                        }
                    }

                } else {

                    $fields[ $key ] = $this->$key;
                }
            }
        }

        return apply_filters( 'erp_hr_get_employee_fields', $fields, $this->id, $this->user );
    }

    /**
     * Get data from ERP employee table
     *
     * @return object the wpdb row object
     */
    private function get_erp_row() {
        global $wpdb;

        if ( $this->id ) {
            $cache_key = 'erp-empl-' . $this->id;
            $row       = wp_cache_get( $cache_key, 'wp-erp' );

            if ( false === $row ) {
                $query = "SELECT e.*, d.title as designation_title, dpt.title as department_title
                    FROM {$wpdb->prefix}erp_hr_employees AS e
                    LEFT JOIN {$wpdb->prefix}erp_hr_designations AS d ON d.id = e.designation
                    LEFT JOIN {$wpdb->prefix}erp_hr_depts AS dpt ON dpt.id = e.department
                    WHERE employee_id = %d";
                $row   = $wpdb->get_row( $wpdb->prepare( $query, $this->id ) );
                wp_cache_set( $cache_key, $row, 'wp-erp' );
            }

            return $row;
        }

        return false;
    }

    /**
     * Get an employee avatar
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

        return get_avatar( $this->id, $size );
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
     * Get the job title
     *
     * @return string
     */
    public function get_job_title() {
        if ( $this->id && isset( $this->erp->designation_title ) ) {
            return stripslashes( $this->erp->designation_title );
        }
    }

    /**
     * Get the department title
     *
     * @return string
     */
    public function get_department_title() {
        if ( $this->id && isset( $this->erp->department_title ) ) {
            return stripslashes( $this->erp->department_title );
        }
    }

    /**
     * Get the employee status
     *
     * @return string
     */
    public function get_status() {
        if ( ! empty( $this->user->status ) ) {
            $statuses = erp_hr_get_employee_statuses();

            if ( array_key_exists( $this->user->status, $statuses ) ) {
                return $statuses[ $this->user->status ];
            }
        }
    }

    /**
     * Get the employee type
     *
     * @return string
     */
    public function get_type() {
        if ( ! empty( $this->user->work_type ) ) {
            $types = erp_hr_get_employee_types();

            if ( array_key_exists( $this->user->work_type, $types ) ) {
                return $types[ $this->user->work_type ];
            }
        }
    }

    /**
     * Get joined date
     *
     * @return string
     */
    public function get_joined_date() {
        if ( ! empty( $this->user->work_joined ) ) {
            return date_i18n( get_option( 'date_format' ), strtotime( $this->user->work_joined ) );
        }
    }

    public function get_educations() {

    }

    public function get_experiences() {

    }
}