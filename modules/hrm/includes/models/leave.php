<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave extends Model {
    protected $table = 'erp_hr_leaves';
    protected $fillable = [ 'request_id', 'date', 'length_hours', 'length_days', 'start_time', 'end_time', 'duration_type'];
}