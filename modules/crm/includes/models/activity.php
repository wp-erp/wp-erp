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

    protected $fillable = [ 'user_id', 'type', 'message', 'email_subject', 'log_type', 'start_date', 'end_date', 'created_by', 'extra' ];

    public $timestamps = true;

    public function created_by() {
        return $this->belongsTo( '\WeDevs\ORM\WP\User', 'created_by');
    }

    public function contact() {
        return $this->belongsTo( '\WeDevs\ERP\Framework\Models\People', 'user_id' );
    }
}