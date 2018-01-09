<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Performance
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Employee_History extends Model {

    protected $table = 'erp_hr_employee_history';

    public $timestamps = false;

    protected $fillable = [ 'user_id', 'module', 'category', 'type', 'comment', 'data', 'date'];

    public function user(){
        return $this->belongsTo('WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id');
    }

}
