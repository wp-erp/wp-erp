<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Work_Experience
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Work_Experience extends Model {
    protected $table = 'erp_hr_work_exp';
    protected $fillable = [ 'employee_id', 'company_name', 'job_title', 'from', 'to', 'description' ];

    public function user(){
        return $this->belongsTo('WeDevs\ERP\HRM\Models\Employee', 'employee_id', 'user_id');
    }
}
