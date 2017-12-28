<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Dependents extends Model {
    protected $table = 'erp_hr_dependents';
    protected $fillable = [ 'employee_id', 'name', 'relation', 'dob' ];

    public function user(){
        return $this->belongsTo('WeDevs\ERP\HRM\Models\Employee', 'user_id', 'employee_id');
    }
}
