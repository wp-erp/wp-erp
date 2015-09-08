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
    protected $fillable = [ 'name', 'unit', 'value', 'color', 'department', 'designation', 'gender', 'marital', 'rate_transition' ];
}