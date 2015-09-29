<?php 
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Work_Experience
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Entitlement extends Model {
    protected $table = 'erp_hr_leave_entitlements';
    protected $fillable = [ 'user_id', 'policy_id', 'days', 'from_date', 'to_date', 'comments', 'status', 'created_by', 'created_on' ];
}