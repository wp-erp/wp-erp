<?php

namespace WeDevs\ERP\Framework;

/**
 * Class Model
 */
class Model extends \WeDevs\ORM\Eloquent\Model {
    protected $prefixed_table = null;

    /**
     * Get the table name with WP prefix
     *
     * @return string
     */
    public function getTable() {
        if ( ! $this->prefixed_table ) {
            $this->prefixed_table = $this->getConnection()->db->prefix . $this->table;
        }

        return $this->prefixed_table;
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setCreatedAt( $value ) {
        $this->{static::CREATED_AT} = current_time( 'mysql' );
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setUpdatedAt( $value ) {
        $this->{static::UPDATED_AT} = current_time( 'mysql' );
    }

    /**
     * Set the table associated with the model.
     *
     * @param string $table
     *
     * @return $this
     */
    public function setTable( $table ) {
        if ( ! empty( $this->table ) ) {
            $table = $this->table;
        }

        if ( ! $this->prefixed_table ) {
            $this->prefixed_table = $this->getConnection()->db->prefix . $table;
        }

        return $this;
    }
}
