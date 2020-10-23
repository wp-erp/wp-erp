<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 */
class Dependents extends Model {
    protected $table = 'erp_hr_dependents';

    protected $fillable = [ 'employee_id', 'name', 'relation', 'dob' ];
}
