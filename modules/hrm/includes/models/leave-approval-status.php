<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Approval_Status
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Approval_Status extends Model {
    protected $table = 'erp_hr_leave_approval_status_new';

    protected $fillable = [
        'leave_request_id', 'approval_status_id', 'approved_by',
        'approved_date', 'forward_to', 'message'
    ];
}