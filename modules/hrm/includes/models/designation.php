<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Designation
 */
class Designation extends Model {
    protected $table = 'erp_hr_designations';

    protected $fillable = [ 'title', 'description', 'status' ];

    public function employees() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Employee', 'designation', 'id' );
    }
}
