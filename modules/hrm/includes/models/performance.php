<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Performance
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Performance extends Model {

    protected $table = 'erp_hr_employee_performance';

    public $timestamps = false;

    protected $fillable = [ 'employee_id', 'reporting_to', 'job_knowledge', 'work_quality', 'attendance', 'communication', 'dependablity', 'reviewer', 'comments', 'completion_date', 'goal_description', 'employee_assessment', 'supervisor', 'supervisor_assessment', 'type', 'performance_date' ];

    public function user(){
        return $this->belongsTo('WeDevs\ERP\HRM\Models\Employee',  'employee_id', 'user_id');
    }

}
