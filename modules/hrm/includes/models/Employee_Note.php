<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Employee_Note extends Model {

    protected $primaryKey = 'id';
    protected $table = 'erp_hr_employee_notes';
    protected $fillable = [ 'user_id', 'comment', 'comment_by' ];

    public function user() {
        return $this->belongsTo('\WeDevs\ERP\HRM\Models\Hr_User', 'comment_by' );
    }
}