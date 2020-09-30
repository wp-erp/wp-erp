<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Request_Detail
 */
class Leave_Request_Detail extends Model {
    protected $table = 'erp_hr_leave_request_details';

    protected $fillable = [
        'leave_request_id', 'leave_approval_status_id',
        'workingday_status', 'user_id', 'leave_date',
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
     * Relation to Leave_Approval_Status model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_approval_status() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Approval_Status', 'leave_request_id', 'leave_request_id' );
    }
}
