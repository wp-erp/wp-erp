<?php
namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class Peoplemeta extends Model {
    protected $primaryKey = 'meta_id';
    protected $table      = 'erp_peoplemeta';
    public $timestamps    = false;
    protected $fillable   = [ 'meta_key', 'meta_value' ];
}
