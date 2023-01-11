<?php

namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class People extends Model {
    protected $primaryKey = 'id';

    protected $table      = 'erp_peoples';

    public $timestamps    = false;

    protected $fillable   = [ 'user_id', 'first_name', 'last_name', 'company', 'email', 'phone', 'mobile',
            'other', 'website', 'fax', 'notes', 'street_1', 'street_2', 'city', 'state', 'postal_code', 'country',
            'currency', 'life_stage', 'hash', 'contact_owner', 'created_by', 'created', ];

    /**
     * Peoplemeta model relation
     *
     * @since 1.1.7
     *
     * @return object
     */
    public function meta() {
        return $this->hasMany( '\WeDevs\ERP\Framework\Models\Peoplemeta', 'erp_people_id' );
    }

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
            return $query->whereHas( 'types', function ( $qry ) use ( $type ) {
                $qry->whereIn( 'name', $type )->whereNull( 'deleted_at' );
            } );
        } elseif ( $type !== 'all' ) {
            return $query->whereHas( 'types', function ( $qry ) use ( $type ) {
                $qry->where( 'name', '=', $type )->whereNull( 'deleted_at' );
            } );
        }

        return $query;
    }

    /**
     * Fetch only trashed people
     *
     * @since 1.0
     *
     * @param collection   $query
     * @param string|array $type
     *
     * @return collection
     */
    public function scopeTrashed( $query, $type ) {
        if ( is_array( $type ) ) {
            return $query->whereHas( 'types', function ( $qry ) use ( $type ) {
                $qry->whereIn( 'name', $type )->whereNotNull( 'deleted_at' );
            } );
        } elseif ( is_string( $type ) ) {
            return $query->whereHas( 'types', function ( $qry ) use ( $type ) {
                $qry->where( 'name', '=', $type )->whereNotNull( 'deleted_at' );
            } );
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
     * @param mixed $type
     *
     * @return mixed
     */
    public function assignType( $type ) {
        return $this->types()->attach( $type );
    }

    /**
     * Remove type from a people
     *
     * @param mixed $type
     *
     * @return mixed
     */
    public function removeType( $type ) {
        return $this->types()->detach( $type );
    }

    /**
     * Temporary trashed a people
     *
     * @param mixed $type
     *
     * @return mixed
     */
    public function softDeleteType( $type ) {
        return $this->types()->updateExistingPivot( $type->id, ['deleted_at' => current_time( 'mysql' ) ] );
    }

    /**
     * Restore for trash
     *
     * @param mixed $type
     *
     * @return mixed
     */
    public function restore( $type ) {
        return $this->types()->updateExistingPivot( $type->id, [ 'deleted_at' => null ] );
    }

    /**
     * Does this people has a particular type?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasType( $name ) {
        foreach ( $this->types as $type ) {
            if ( $type->name == $name ) {
                return true;
            }
        }

        return false;
    }
}
