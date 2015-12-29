<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Activity extends Model {
    protected $table = 'erp_crm_customer_activities';

    protected $fillable = [ 'user_id', 'type', 'message', 'email_subject', 'log_type', 'log_time', 'log_date', 'created_by' ];

    public $timestamps = true;

    public function created_by() {
        return $this->belongsTo( '\WeDevs\ORM\WP\User', 'created_by');
    }

    public function contact() {
        return $this->belongsTo( '\WeDevs\ERP\Framework\Models\People', 'user_id' );
    }
}