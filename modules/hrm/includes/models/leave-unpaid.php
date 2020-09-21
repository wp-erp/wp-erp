<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leaves_Unpaid
 */
class Leaves_Unpaid extends Model {
    protected $table = 'erp_hr_leaves_unpaid';

    protected $fillable = [
        'leave_id', 'leave_request_id', 'leave_approval_status_id',
        'user_id', 'f_year', 'days', 'amount', 'total',
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
    public function entitlement() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'trn_id' );
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
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Financial_Year', 'f_year', 'id' );
    }
}
