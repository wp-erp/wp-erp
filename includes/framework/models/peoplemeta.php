<?php
namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class Peoplemeta extends Model {
    protected $primaryKey = 'meta_id';
    protected $table      = 'erp_peoplemeta';
    public $timestamps    = false;
    protected $fillable   = [ 'meta_key', 'meta_value' ];

    /**
     * Available CRM Meta
     *
     * @since 1.1.7
     *
     * @param object $query
     *
     * @return object Query Builder
     */
    public function scopeAvailableMeta( $query ) {
        $meta_keys = erp_crm_get_contact_meta_fields();

        return $query->whereIn( 'meta_key', $meta_keys );
    }
}
