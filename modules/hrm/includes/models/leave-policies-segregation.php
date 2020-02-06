<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Policies_Segregation
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Policies_Segregation extends Model {
    protected $table = 'erp_hr_leave_policies_segregation_new';

    protected $fillable = [
        'leave_policy_id', 'jan', 'feb', 'mar', 'apr', 'may',
        'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'decem'
    ];

    /**
     * Relation to Leave_Policy model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave_policy() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Policy' );
    }    
}