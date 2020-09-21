<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Approval_Status
 */
class Leave_Approval_Status extends Model {
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
     * Relation to Leave_Entitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'trn_id' );
    }

    /**
     * Relation to Leave_Request_Detail model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function details() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Request_Detail', 'leave_request_id', 'leave_request_id' );
    }

    /**
     * Relation to Leave_Request model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_request() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Request' );
    }

    /**
     * Relation to Leaves_Unpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaids() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leaves_Unpaid', 'leave_request_id', 'leave_request_id' );
    }

    /**
     * Relation to Hr_User model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_approved_by() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Hr_User', 'approved_by', 'id' );
    }
}
