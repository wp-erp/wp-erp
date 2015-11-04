<?php
namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class People extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_peoples';
    public $timestamps    = false;
}
