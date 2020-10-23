<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Education
 */
class Education extends Model {
    protected $table = 'erp_hr_education';

    protected $fillable = [ 'employee_id', 'school', 'degree', 'field', 'finished', 'notes', 'interest' ];
}
