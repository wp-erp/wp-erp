<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Announcement extends Model {
    protected $table = 'erp_hr_announcement';
    protected $fillable = [ 'user_id', 'post_id', 'status' ];
    public $timestamps = false;
}