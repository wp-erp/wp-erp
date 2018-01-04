<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Department
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Department extends Model {
	protected $primaryKey = 'id';
    protected $table = 'erp_hr_depts';
    protected $fillable = [ 'title', 'description', 'lead', 'parent', 'status' ];

//    public function employees(){
//        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Employee', 'department', 'id' );
//    }
}
