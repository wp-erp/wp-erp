<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Department
 */
class Department extends Model {
    protected $primaryKey = 'id';

    protected $table = 'erp_hr_depts';

    protected $fillable = [ 'title', 'description', 'lead', 'parent', 'status' ];
}
