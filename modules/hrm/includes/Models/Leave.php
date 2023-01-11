<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave
 */
class Leave extends Model {
    protected $table = 'erp_hr_leaves';

    protected $fillable = [ 'name', 'description' ];

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
     * Relation to Leave_Policies model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function policies() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeavePolicy' );
    }

    /**
     * Relation to LeaveEntitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveEntitlement' );
    }

    /**
     * Relation to LeaveRequest model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function requests() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveRequest' );
    }

    /**
     * Relation to LeaveEncashmentRequest model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function encashments() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeaveEncashmentRequest' );
    }

    /**
     * Relation to LeavesUnpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaids() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\LeavesUnpaid' );
    }
}
