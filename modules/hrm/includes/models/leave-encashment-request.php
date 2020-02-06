<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Encashment_Request extends Model {
    protected $table = 'erp_hr_leave_encashment_requests_new';

    protected $fillable = [
        'user_id', 'leave_id', 'approved_by',
        'approved_date', 'forward_to', 'message'
    ];
}