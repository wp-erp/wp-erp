<?php

namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 */
class Activity extends Model {
    protected $table = 'erp_crm_customer_activities';

    protected $fillable = [ 'user_id', 'type', 'message', 'email_subject', 'log_type', 'start_date', 'end_date', 'sent_notification', 'created_by', 'extra', 'created_at' ];

    public $timestamps = true;

    public function created_by() {
        return $this->belongsTo( '\WeDevs\ORM\WP\User', 'created_by' );
    }

    public function contact() {
        return $this->belongsTo( '\WeDevs\ERP\Framework\Models\People', 'user_id' );
    }

    public static function scopeSchedules( $query ) {
        return $query->where( 'type', 'log_activity' )
            ->where( 'sent_notification', false );
    }
}
