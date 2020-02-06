<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Request_Detail extends Model {
    protected $table = 'erp_hr_leave_request_details_new';

    protected $fillable = [
        'leave_request_id', 'leave_id', 'day_status_id',
        'user_id', 'leave_date', 'status'
    ];
}