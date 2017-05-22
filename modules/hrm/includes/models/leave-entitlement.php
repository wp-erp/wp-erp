<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Entitlement
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Entitlement extends Model {
    protected $table = 'erp_hr_leave_entitlements';
    protected $fillable = [
        'user_id', 'policy_id', 'days', 'from_date',
        'to_date', 'comments', 'status', 'created_by', 'created_on'
    ];

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
