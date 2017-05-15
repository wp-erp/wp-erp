<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Policies
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Policies extends Model {
    protected $table = 'erp_hr_leave_policies';
    protected $fillable = [
        'name', 'value', 'color', 'department', 'designation', 'gender',
        'marital', 'description', 'location', 'effective_date', 'activate', 'execute_day'
    ];

    /**
     * Relation to Leave_Entitlement model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'policy_id' );
    }

    /**
     * Relation to Leave_request model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function leave_requests() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_request', 'policy_id' );
    }
}
