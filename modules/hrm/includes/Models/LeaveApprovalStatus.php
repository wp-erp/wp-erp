<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class LeaveApprovalStatus
 */
class LeaveApprovalStatus extends Model {
    protected $table = 'erp_hr_leave_approval_status';

    protected $fillable = [
        'leave_request_id', 'approval_status_id', 'approved_by',
        'approved_date', 'message',
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
     * Relation to LeaveEntitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveEntitlement', 'trn_id' );
    }

    /**
     * Relation to LeaveRequestDetail model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function details() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveRequestDetail', 'leave_request_id', 'leave_request_id' );
    }

    /**
     * Relation to LeaveRequest model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_request() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\LeaveRequest' );
    }

    /**
     * Relation to LeavesUnpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaids() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeavesUnpaid', 'leave_request_id', 'leave_request_id' );
    }

    /**
     * Relation to HrUser model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_approved_by() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\HrUser', 'approved_by', 'id' );
    }
}
