<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leaves_Unpaid
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leaves_Unpaid extends Model {
    protected $table = 'erp_hr_leaves_new';

    protected $fillable = [
        'leave_id', 'leave_request_id', 'leave_approval_status_id',
        'user_id', 'days', 'amount', 'total', 'status'
    ];
}