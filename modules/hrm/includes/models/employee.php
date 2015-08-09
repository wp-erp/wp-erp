<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Employee extends Model {

    protected $table = 'erp_hr_employees';
    public $timestamps = false;
    protected $fillable = [ 'user_id', 'employee_id', 'designation', 'department', 'location', 'hiring_source', 'hiring_date', 'termination_data', 'date_of_birth', 'reporting_to', 'pay_rate', 'pay_type', 'type', 'status' ];

}