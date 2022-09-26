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
        return $this->getConnection()->db->prefix . 'users';
    }
}
