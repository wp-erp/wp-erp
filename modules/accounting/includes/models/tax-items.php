<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Tax_Items extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_tax_items';
    public $timestamps    = false;
    protected $fillable   = ['tax_id', 'component_name', 'agency_name', 'tax_rate'];
}