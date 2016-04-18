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
            'currency', 'created' ];


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

            return $query->whereHas( 'types', function( $qry ) use( $type ) {
                $qry->whereIn( 'name', $type );
            });

        } elseif ( $type !== 'all' ) {

            return $query->whereHas( 'types', function( $qry ) use( $type ) {
                $qry->where( 'name', '=', $type );
            });
        }

        return $query;
    }

    /**
     * Get the types of peoples
     *
     * @return object
     */
    public function types() {
        global $wpdb;

        return $this->belongsToMany( '\WeDevs\ERP\Framework\Models\PeopleTypes', $wpdb->prefix . 'erp_people_type_relations' );
    }

    /**
     * Assign type to a people
     *
     * @param  mixed  $type
     *
     * @return mixed
     */
    public function assignType( $type ) {
        return $this->types()->attach( $type );
    }

    /**
     * Remove type from a people
     *
     * @param  mixed  $type
     *
     * @return mixed
     */
    public function removeType( $type ) {
        return $this->types()->detach( $type );
    }

    /**
     * Does this people has a particular type?
     *
     * @param  string   $name
     *
     * @return boolean
     */
    public function hasType( $name ) {
        foreach ( $this->types->toArray() as $type) {
            if ( $type->name == $name ) {
                return true;
            }
        }

        return false;
    }
}
