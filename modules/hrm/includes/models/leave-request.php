<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Work_Experience
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_request extends Model {
    protected $table = 'erp_hr_leave_requests';
    protected $fillable = [ 'user_id', 'policy_id', 'days', 'start_date', 'end_date', 'comments', 'reason', 'status'];
}