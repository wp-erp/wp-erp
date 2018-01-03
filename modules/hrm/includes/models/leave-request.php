<?php
namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_request
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Leave_request extends Model {
    /**
     * Custom created_at field
     *
     * @since 1.2.0
     */
    const CREATED_AT = 'created_on';

    /**
     * Custom updated_at field
     *
     * @since 1.2.0
     */
    const UPDATED_AT = 'updated_on';
    protected $primaryKey = 'id';

    protected $table = 'erp_hr_leave_requests';
    protected $fillable = [
        'user_id', 'policy_id', 'days', 'start_date',
        'end_date', 'comments', 'reason', 'status'
    ];

    /**
     * Relation to Leave model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function leave() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave', 'request_id' );
    }

    /**
     * Relation to Leave_Policies model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function policy() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave_Policies', 'policy_id' );
    }

    /**
     * Relation to Leave model
     *
     * @since 1.2.0
     *
     * @return object
     */
    public function employee() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id' );
    }

    public function scopeJoinWithPolicy( $query ) {
        global $wpdb;
        return $query->leftJoin( "{$wpdb->prefix}erp_hr_leave_policies", "{$wpdb->prefix}erp_hr_leave_policies.id", "=", "{$wpdb->prefix}erp_hr_leave_requests.policy_id" );
    }

    public function scopeJoinWithEn( $query ) {
        global $wpdb;
        return $query->leftJoin( "{$wpdb->prefix}erp_hr_leave_entitlements", "{$wpdb->prefix}erp_hr_leave_entitlements.policy_id", "=", "{$wpdb->prefix}erp_hr_leave_requests.policy_id" );
    }


}
