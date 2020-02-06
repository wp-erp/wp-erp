<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Entitlement
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Entitlement extends Model {
    protected $table = 'erp_hr_leave_entitlements_new';

    protected $fillable = [
        'user_id', 'leave_id', 'created_by', 'trn_id', 'trn_type',
        'day_in', 'day_out', 'description', 'f_year'
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
     * Relation to Leave_Policy model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function policy() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Policy', 'trn_id' );
    }

    /**
     * Relation to Leave_Approval_Status model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_approval_status() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Approval_Status', 'trn_id' );
    }

    /**
     * Relation to Leave_Encashment_Request model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function encashment_request() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Encashment_Request', 'trn_id' );
    }

    /**
     * Relation to Leaves_Unpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaids() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leaves_Unpaid', 'trn_id' );
    }
}
