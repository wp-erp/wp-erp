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
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Policy' );
    }

    /**
     * Relation to Leave_Entitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlements() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Entitlement' );
    }

    /**
     * Relation to Leave_Request model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function requests() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Request' );
    }

    /**
     * Relation to Leave_Encashment_Request model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function encashments() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Encashment_Request' );
    }

    /**
     * Relation to Leaves_Unpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaids() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leaves_Unpaid' );
    }
}
