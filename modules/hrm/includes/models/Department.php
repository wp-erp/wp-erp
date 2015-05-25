<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Department
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Department extends Model {
    protected $table = 'erp_hr_depts';
    protected $fillable = [ 'company_id', 'title', 'description', 'lead', 'parent', 'status' ];
}