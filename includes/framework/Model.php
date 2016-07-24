<?php

namespace WeDevs\ERP\Framework;

/**
 * Class Model
 *
 * @package WeDevs\ERP\Framework
 */
class Model extends \WeDevs\ORM\Eloquent\Model {

    /**
     * Get the table name with WP prefix
     *
     * @return string
     */
    public function getTable() {
        return $this->getConnection()->db->prefix . $this->table;
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setCreatedAt( $value ) {
        $this->{static::CREATED_AT} = current_time( 'mysql' );
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setUpdatedAt( $value ) {
        $this->{static::UPDATED_AT} = current_time( 'mysql' );
    }
}