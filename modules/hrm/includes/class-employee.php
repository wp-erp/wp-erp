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

        if ( $this->id ) {
            foreach ($fields as $key => $value) {
                if ( is_array( $value ) ) {

                    if ( $key == 'avatar' ) {
                        $avatar_id                 = (int) $this->user->photo_id;
                        $fields['avatar']['id']    = $avatar_id;
                        $fields['avatar']['image'] = $this->get_avatar();

                        if ( $avatar_id ) {
                            $fields['avatar']['url'] = wp_get_attachment_url( $avatar_id );
                        }
                        continue;
                    }

                    if ( $key == 'name' ) {
                        $fields['name']['full_name'] = $this->get_full_name();
                    }

                    foreach ($value as $n_key => $n_value) {
                        switch ( $n_key ) {
                            case 'department':
                                $fields[ $key ][ $n_key ] = (int) $this->erp->department;
                                break;

                            case 'department_title':
                                $fields[ $key ][ $n_key ] = $this->erp->dept_title;
                                break;

                            case 'designation':
                                $fields[ $key ][ $n_key ] = (int) $this->erp->designation;
                                break;

                            case 'designation_title':
                                $fields[ $key ][ $n_key ] = $this->erp->job_title;
                                break;

                            case 'company_id':
                                $fields[ $key ][ $n_key ] = (int) $this->erp->company_id;
                                break;

                            default:
                                $fields[ $key ][ $n_key ] = $this->user->$n_key;
                                break;
                        }
                    }

                } else {

                    switch ( $key ) {
                        case 'id':
                            $fields[ $key ] = $this->id;
                            break;

                        default:
                            $fields[ $key ] = $this->user->$key;
                            break;
                    }
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
                $query = "SELECT e.*, d.title as job_title, dpt.title as dept_title
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

    public function get_avatar( $size = 32 ) {
        if ( $this->id && ! empty( $this->user->photo_id ) ) {
            $image = wp_get_attachment_thumb_url( $this->user->photo_id );
            return sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="%2$s" width="%2$s" />', $image, $size );
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
        if ( $this->id && isset( $this->erp->job_title ) ) {
            return stripslashes( $this->erp->job_title );
        }
    }

    /**
     * Get the department title
     *
     * @return string
     */
    public function get_department_title() {
        if ( $this->id && isset( $this->erp->dept_title ) ) {
            return stripslashes( $this->erp->dept_title );
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
        if ( ! empty( $this->user->type ) ) {
            $types = erp_hr_get_employee_types();

            if ( array_key_exists( $this->user->type, $types ) ) {
                return $types[ $this->user->type ];
            }
        }
    }

    /**
     * Get joined date
     *
     * @return string
     */
    public function get_joined_date() {
        if ( ! empty( $this->user->joined ) ) {
            return date_i18n( get_option( 'date_format' ), strtotime( $this->user->joined ) );
        }
    }

    public function get_educations() {

    }

    public function get_experiences() {

    }
}