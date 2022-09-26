<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class LeaveHoliday
 */
class LeaveHoliday extends Model {
    protected $table = 'erp_hr_holiday';

    protected $fillable = [ 'title', 'start', 'end', 'description', 'range_status', 'created_at', 'updated_at' ];

    public function getTitleAttribute( $title ) {
        return stripslashes( $title );
    }
}
