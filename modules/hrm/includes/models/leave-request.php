<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_request
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_Request extends Model {
    protected $table = 'erp_hr_leave_requests_new';

    protected $fillable = [
        'user_id', 'leave_id', 'day_status_id', 'days',
        'start_date', 'end_date', 'reason'
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
    //     return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Policies', 'policy_id' );
    // }

    /**
     * Relation to Leave model
     *
     * @since 1.2.0
     *
     * @return object
     */
    // public function employee() {
    //     return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id' );
    // }

    // public function scopeJoinWithPolicy( $query ) {
    //     global $wpdb;
    //     return $query->leftJoin( "{$wpdb->prefix}erp_hr_leave_policies", "{$wpdb->prefix}erp_hr_leave_policies.id", "=", "{$wpdb->prefix}erp_hr_leave_requests.policy_id" );
    // }

    // public function scopeJoinWithEn( $query ) {
    //     global $wpdb;
    //     return $query->leftJoin( "{$wpdb->prefix}erp_hr_leave_entitlements", "{$wpdb->prefix}erp_hr_leave_entitlements.policy_id", "=", "{$wpdb->prefix}erp_hr_leave_requests.policy_id" );
    // }


}
