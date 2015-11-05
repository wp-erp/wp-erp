<?php
namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class People extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_peoples';
    public $timestamps    = false;
    
    /**
     * Fetch people with types
     * 
     * @param object $query
     * @param string $type
     * 
     * @return object
     */
    public function scopeType( $query, $type = 'customer' ) {
        return $query->where( 'type', '=', $type );
    }
}
