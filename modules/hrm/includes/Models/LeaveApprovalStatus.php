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
        'approved_date', 'message', 'forward_to',
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

    /**
     * Relation to the user a request was forwarded to (Advanced Leave multilevel).
     *
     * Both the pro AJAX handler and the v2 approval-chain endpoint have always
     * read `$approval->leave_forward_to->display_name`, but the relation was
     * never declared and `forward_to` was not fillable — so the recipient was
     * silently dropped on write and the column rendered `-` on read.
     *
     * @since 1.18.1
     *
     * @return object
     */
    public function leave_forward_to() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\HrUser', 'forward_to', 'id' );
    }
}
