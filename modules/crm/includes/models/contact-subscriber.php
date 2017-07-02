<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class ContactSubscriber extends Model {
    protected $table = 'erp_crm_contact_subscriber';

    protected $fillable = [ 'user_id', 'group_id', 'status', 'subscribe_at', 'unsubscribe_at', 'hash' ];

    public $timestamps = false;

    public function groups() {
        return $this->belongsTo( '\WeDevs\ERP\CRM\Models\ContactGroup', 'group_id' );
    }

    /**
     * Join Contact Group table
     *
     * @since 1.2.2
     *
     * @param object  $query
     * @param integer $people_id
     * @param boolean $withPrivate
     *
     * @return object
     */
    public function scopeGetSubscriberGroups( $query, $people_id, $withPrivate = false ) {
        $prefix     = $query->getQuery()->getConnection()->db->prefix;
        $group_tbl  = $prefix . 'erp_crm_contact_group';
        $subs_tbl   = $prefix . $this->table;

        $query->select( $subs_tbl . '.*', $group_tbl . '.name', $group_tbl . '.description', $group_tbl . '.private' )
              ->leftJoin( $group_tbl, $subs_tbl . '.group_id', '=', $group_tbl . '.id' )
              ->where( $subs_tbl . '.user_id', $people_id );

        if ( ! $withPrivate ) {
            $query->whereNull( $group_tbl . '.private' );
        }

        return $query;
    }
}
