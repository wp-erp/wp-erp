<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Work_Experience
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Performance extends Model {

    protected $table = 'erp_hr_employee_performance';

    public $timestamps = false;

    protected $fillable = [ 'employee_id', 'reporting_to', 'job_knowledge', 'work_quality', 'attendance', 'communication', 'dependablity', 'reviewer', 'comments', 'completion_date', 'goal_description', 'employee_assessment', 'supervisor', 'supervisor_assessment', 'type', 'performance_date' ];

}