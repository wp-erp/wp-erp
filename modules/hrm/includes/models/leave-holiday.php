<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Holiday
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Holiday extends Model {
    protected $table = 'erp_hr_holiday';
    protected $fillable = [ 'title', 'start', 'end', 'description', 'range_status', 'created_at', 'updated_at' ];

    public function getTitleAttribute( $title ) {
        return stripslashes( $title );
    }
}
