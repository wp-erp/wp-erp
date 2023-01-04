<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class LeavePoliciesSegregation
 */
class LeavePoliciesSegregation extends Model {
    protected $table = 'erp_hr_leave_policies_segregation';

    protected $fillable = [
        'leave_policy_id', 'jan', 'feb', 'mar', 'apr', 'may',
        'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'decem',
    ];

    /**
     * Created at date format
     */
    public function setCreatedAtAttribute() {
        $this->attributes['created_at'] = erp_current_datetime()->getTimestamp();
    }

    /**
     * Updated at date format
     */
    public function setUpdatedAtAttribute() {
        $this->attributes['updated_at'] = erp_current_datetime()->getTimestamp();
    }

    /**
     * Relation to LeavePolicy model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_policy() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\LeavePolicy' );
    }
}
