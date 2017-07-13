<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class ContactGroup extends Model {
    protected $table = 'erp_crm_contact_group';

    protected $fillable = [ 'name', 'private', 'description' ];

    public $timestamps = true;

    public function contact_subscriber() {
        return $this->hasMany( 'WeDevs\ERP\CRM\Models\ContactSubscriber', 'group_id' );
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
    public function scopeGetGroupSubscriber( $query, $people_id, $withPrivate = false ) {
        $prefix     = $query->getQuery()->getConnection()->db->prefix;
        $group_tbl  = $prefix . $this->table;
        $subs_tbl   = $prefix . 'erp_crm_contact_subscriber';

        $query->select( $group_tbl . '.id', $group_tbl . '.name', $group_tbl . '.private', $subs_tbl . '.user_id', $subs_tbl . '.status', $subs_tbl . '.subscribe_at', $subs_tbl . '.unsubscribe_at', $subs_tbl . '.hash' )
              ->leftJoin( $subs_tbl, $group_tbl . '.id', '=', $subs_tbl . '.group_id' )
              ->where( $subs_tbl . '.user_id', $people_id );

        if ( ! $withPrivate ) {
            $query->whereNull( $group_tbl . '.private' );
        }

        return $query;
    }

}
