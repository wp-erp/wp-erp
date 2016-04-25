<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class ActivityUser extends Model {
    protected $table = 'erp_crm_activities_task';

    protected $fillable = [ 'activity_id', 'user_id' ];

    public $timestamps = false;

}
