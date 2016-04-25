<?php
namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class PeopleTypes extends Model {
    protected $table      = 'erp_people_types';
    public $timestamps    = false;
    protected $fillable   = [ 'name' ];

    /**
     * Filter types by name
     *
     * @param  object  $query
     * @param  string  $name
     *
     * @return object
     */
    public function scopeName( $query, $name ) {
        return $query->where( 'name', '=', $name );
    }
}
