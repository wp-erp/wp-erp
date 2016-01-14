<?php
namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class People extends Model {

    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table      = 'erp_peoples';
    public $timestamps    = false;
    protected $fillable   = [ 'user_id', 'first_name', 'last_name', 'company', 'email', 'phone', 'mobile',
            'other', 'website', 'fax', 'notes', 'street_1', 'street_2', 'city', 'state', 'postal_code', 'country',
            'currency', 'type', 'created' ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    /**
     * Fetch people with types
     *
     * @param object $query
     * @param string $type
     *
     * @return object
     */
    public function scopeType( $query, $type ) {

        if ( is_array( $type ) ) {
            return $query->whereIn( 'type', $type );
        } else {
            if ( $type == 'all' ) {
                return $query->where( 'type', '!=', '' );
            }
            return $query->where( 'type', '=', $type );
        }

    }
}
