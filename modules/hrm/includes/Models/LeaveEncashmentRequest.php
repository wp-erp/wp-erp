<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class LeaveEncashmentRequest
 */
class LeaveEncashmentRequest extends Model {
    protected $table = 'erp_hr_leave_encashment_requests';

    protected $fillable = [
        'user_id', 'leave_id', 'approved_by', 'approval_status_id',
        'f_year', 'encash_days', 'forward_days', 'amount', 'total',
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
     * Relation to User model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function approver() {
        return $this->belongsTo( 'WeDevs\ORM\WP\User', 'approved_by', 'ID' );
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
