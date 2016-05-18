<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Chart_Type extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_chart_types';
    protected $fillable   = [ 'name', 'class_id'];
}