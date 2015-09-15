<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Work_Experience
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Policies extends Model {
    protected $table = 'erp_hr_leave_policies';
    protected $fillable = [ 'name', 'value', 'color', 'department', 'designation', 'gender', 'marital', 'description', 'location', 'effective_date', 'activate', 'execute_day' ];
}