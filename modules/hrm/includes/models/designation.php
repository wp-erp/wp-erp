<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Department
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Designation extends Model {
    protected $table = 'erp_hr_designations';
    protected $fillable = [ 'title', 'description', 'status' ];
}