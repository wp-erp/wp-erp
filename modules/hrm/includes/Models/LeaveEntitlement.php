<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class LeaveEntitlement
 */
class LeaveEntitlement extends Model {
    protected $table = 'erp_hr_leave_entitlements';

    protected $fillable = [
        'user_id', 'leave_id', 'created_by', 'trn_id', 'trn_type',
        'day_in', 'day_out', 'description', 'f_year',
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
     * Relation to LeaveApprovalStatus model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_requests() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveRequest', 'leave_entitlement_id', 'id' )->orderBy( 'id', 'desc' );
    }

    /**
     * Relation to LeavePolicy model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function policy() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\LeavePolicy', 'trn_id' );
    }

    /**
     * Relation to LeaveApprovalStatus model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_approval_status() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\LeaveApprovalStatus', 'trn_id' );
    }

    /**
     * Relation to LeaveEncashmentRequest model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function encashment_request() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\LeaveEncashmentRequest', 'trn_id' );
    }

    /**
     * Relation to LeavesUnpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaids() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\LeavesUnpaid', 'trn_id' );
    }

    /**
     * Relation to Employee model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function employee() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id' );
    }

    /**
     * Relation to Financial Year
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function financial_year() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\FinancialYear', 'f_year', 'id' );
    }
}
