<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Policies
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Policy extends Model {
    protected $table = 'erp_hr_leave_policies';

    protected $fillable = [
        'leave_id', 'old_policy_id', 'description', 'days', 'color',
        'department_id', 'location_id', 'designation_id', 'f_year',
        'forward_status', 'encashment_status', 'gender', 'marital',
        'applicable_from_days', 'accrued_amount', 'accrued_days'
    ];

    /**
     * Relation to Leave model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function leave() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave' );
    }

    /**
     * Relation to Leave_Entitlement model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'trn_id' );
    }

    /**
     * Relation to Leave_Policies_Segregation
     * 
     * @since 1.5.15
     * 
     * @return object
     */
    public function segregation() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave_Policies_Segregation' );
    }
}
