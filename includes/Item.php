<?php

namespace WeDevs\ERP;

/**
 * Item abstract class
 *
 * Utilizes this class to get and set details about an *item*
 */
abstract class Item {

    /**
     * Item ID
     *
     * @var int
     */
    public $id = 0;

    /**
     * Item data
     *
     * @var object
     */
    public $data = null;

    /**
     * Item name
     *
     * @var string
     */
    public $name = '';

    /**
     * Item types
     *
     * @var array
     */
    public $types = array();

    /**
     * Initialize a item
     *
     * @param int|object  the item numeric id or a wpdb row
     */
    public function __construct( $item = null ) {
        if ( is_object( $item ) ) {
            $this->populate( $item );
        } elseif ( is_int( $item ) ) {
            $fetched = $this->get_by_id( $item );
            $this->populate( $fetched );
        }
    }

    /**
     * Magic method to get item data values
     *
     * @param  string
     *
     * @return string
     */
    public function __get( $key ) {
        if ( isset( $this->data->$key ) ) {
            return is_array( $this->data->$key ) ? \stripslashes_deep( $this->data->$key ) : stripslashes( $this->data->$key );
        }
    }

    /**
     * [populate description]
     *
     * @param  object  the item wpdb object
     *
     * @return void
     */
    protected function populate( $item ) {
        $this->id   = (int) $item->id;
        $this->name = stripslashes( $item->title );
        $this->data = $item;
    }

    /**
     * Get a item by ID
     *
     * @param  int  item id
     *
     * @return object wpdb object
     */
    abstract protected function get_by_id( $item_id );
}
