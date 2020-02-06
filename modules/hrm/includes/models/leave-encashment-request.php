<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Encashment_Request
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Encashment_Request extends Model {
    protected $table = 'erp_hr_leave_encashment_requests_new';

    protected $fillable = [
        'user_id', 'leave_id', 'approved_by',
        'approved_date', 'forward_to', 'message'
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
}