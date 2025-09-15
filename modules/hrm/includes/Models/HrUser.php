<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ORM\WP\User;

/**
 * Class HrUser
 */
class HrUser extends User {

    // protected $table = 'wp_users';
    public $timestamps = false;

    public function notes() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Employee_Note', 'user_id' )->orderBy( 'created_at', 'desc' );
    }

    public function getTable() {
        $default_user_table = $this->getConnection()->db->prefix . 'users';

        /**
         * Filter the table name used for the HrUser model.
         *
         * @param string $default_user_table The default table name.
         * @param HrUser $this          The current model instance.
         */
        return apply_filters( 'erp_hrm_user_table', $default_user_table, $this );
    }
}
