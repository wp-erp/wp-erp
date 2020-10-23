<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Policies
 */
class Leave_Policy extends Model {
    protected $table = 'erp_hr_leave_policies';

    protected $fillable = [
        'leave_id', 'description', 'days', 'color', 'forward_default',
        'department_id', 'location_id', 'designation_id', 'f_year', 'apply_for_new_users',
        'carryover_days', 'carryover_uses_limit', 'encashment_days',
        'encashment_based_on', 'gender', 'marital', 'applicable_from_days',
        'accrued_max_days', 'accrued_amount', 'accrued_based_on', 'halfday_enable', 'employee_type',
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
     * Relation to Leave model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave' );
    }

    /**
     * Relation to Leave_Entitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'trn_id', 'id' )->where( 'trn_type', '=', 'leave_policies' );
    }

    /**
     * Relation to Leave_Policies_Segregation
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function segregation() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave_Policies_Segregation', 'leave_policy_id' );
    }

    /**
     * Relation to Department
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function department() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Department' );
    }

    /**
     * Relation to Designation
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function designation() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Designation' );
    }

    /**
     * Relation to Financial Year
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function financial_year() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Financial_Year', 'f_year', 'id' );
    }

    /**
     * Relation to Company Locations
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function location() {
        return $this->belongsTo( 'WeDevs\ERP\Admin\Models\Company_Locations', 'location_id', 'id' );
    }
}
