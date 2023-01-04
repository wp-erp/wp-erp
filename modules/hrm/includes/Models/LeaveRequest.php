<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_request
 */
class LeaveRequest extends Model {
    protected $table = 'erp_hr_leave_requests';

    protected $fillable = [
        'user_id', 'leave_id', 'leave_entitlement_id', 'day_status_id', 'days',
        'start_date', 'end_date', 'reason', 'last_status', 'created_by',
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
     * Relation to LeaveRequestDetail model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function details() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveRequestDetail', 'leave_request_id', 'id' );
    }

    /**
     * Relation to LeaveApprovalStatus model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function approval_status() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveApprovalStatus', 'leave_request_id', 'id' )->orderBy( 'id', 'desc' );
    }

    /**
     * Relation to LeaveApprovalStatus model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function latest_approval_status() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\LeaveApprovalStatus', 'leave_request_id', 'id' )->orderBy( 'id', 'desc' );
    }

    /**
     * Relation to LeavesUnpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaid() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\LeavesUnpaid', 'leave_request_id', 'id' );
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
     * Relation to Leave Entitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlement() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\LeaveEntitlement', 'id', 'leave_entitlement_id' );
    }
}
