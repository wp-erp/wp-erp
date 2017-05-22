<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Employee
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Employee extends Model {

    use SoftDeletes;

    protected $table = 'erp_hr_employees';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'employee_id', 'designation', 'department', 'location',
        'hiring_source', 'hiring_date', 'termination_data', 'date_of_birth',
        'reporting_to', 'pay_rate', 'pay_type', 'type', 'status', 'delete_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'date_of_birth'];

    /**
     * Relation to Leave_request model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function leave_requests() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_request', 'user_id', 'user_id' );
    }
}

