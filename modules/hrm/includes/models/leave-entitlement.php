<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_Entitlement
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Entitlement extends Model {
    protected $table = 'erp_hr_leave_entitlements_new';

    protected $fillable = [
        'user_id', 'leave_id', 'created_by', 'trn_id', 'trn_type',
        'day_in', 'day_out', 'description', 'f_year'
    ];

    /**
     * Relation to Leave model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave' );
    }

    /**
     * Relation to Leave_Policies model
     *
     * @since 1.2.0
     *
     * @return object
     */
    // public function policy() {
    //     return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Policy', 'leave_id' );
    // }

    // public function leaves(){
    //     return $this->hasMany('\WeDevs\ERP\HRM\Models\Leave_request', 'policy_id', 'policy_id' );
    // }

    // public function employee(){
    //     return $this->belongsTo('\WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id' );
    // }

    // public function scopeJoinWithPolicy( $query ) {
    //     global $wpdb;
    //     return $query->leftJoin( "{$wpdb->prefix}erp_hr_leave_policies", "{$wpdb->prefix}erp_hr_leave_policies.id", "=", "{$wpdb->prefix}erp_hr_leave_entitlements.policy_id" );
    // }
}
