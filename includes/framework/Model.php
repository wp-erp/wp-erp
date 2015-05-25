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
}