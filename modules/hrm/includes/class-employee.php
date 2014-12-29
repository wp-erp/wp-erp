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
    public function __construct( $employee ) {

        if ( is_int( $employee ) ) {

            $this->id   = $employee;
            $this->user = get_user_by( 'id', $employee );

        } elseif ( $employee instanceof WP_User ) {

            $this->id   = $employee->ID;
            $this->user = $employee;

        } elseif ( is_email( $employee ) ) {

            $this->user = get_user_by( 'email', $employee );
            $this->id   = $this->user->ID;
        }
    }

    public function get_job_title() {

    }

    public function get_department() {

    }

    public function get_status() {

    }

    public function get_joined_date() {

    }

    public function get_educations() {

    }

    public function get_experiences() {

    }
}