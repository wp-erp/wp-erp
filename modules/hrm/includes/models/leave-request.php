<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_request
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_request extends Model {
    protected $table = 'erp_hr_leave_requests';
    protected $fillable = [
        'user_id', 'policy_id', 'days', 'start_date',
        'end_date', 'comments', 'reason', 'status'
    ];

    /**
     * Relation to Leave model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function leave() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave', 'request_id' );
    }

    /**
     * Relation to Leave_Policies model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function policy() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Policies', 'policy_id' );
    }
}
