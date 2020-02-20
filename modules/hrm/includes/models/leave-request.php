<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_request
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Request extends Model {
    protected $table = 'erp_hr_leave_requests';

    protected $fillable = [
        'user_id', 'leave_id', 'day_status_id', 'days',
        'start_date', 'end_date', 'reason'
    ];

    /**
     * Relation to Leave model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function leave() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave' );
    }

    /**
     * Relation to Leave_Request_Detail model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function details() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Request_Detail' );
    }

    /**
     * Relation to Leave_Approval_Status model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function leave_approval_status() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Approval_Status', 'leave_request_id' );
    }

    /**
     * Relation to Leaves_Unpaid model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function unpaid() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leaves_Unpaid' );
    }

    /**
     * Relation to Employee model
     *
     * @since 1.5.15
     *
     * @return object
     */
    public function employee() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id' );
    }
}
