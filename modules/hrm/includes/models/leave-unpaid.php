<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leaves_Unpaid
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leaves_Unpaid extends Model {
    protected $table = 'erp_hr_leaves_new';

    protected $fillable = [
        'leave_id', 'leave_request_id', 'leave_approval_status_id',
        'user_id', 'days', 'amount', 'total', 'status'
    ];

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
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'trn_id' );
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
}