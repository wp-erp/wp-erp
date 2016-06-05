<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Chart_Classes extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_chart_classes';
    protected $fillable   = [ 'name'];

    public function types() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Chart_Type', 'class_id' );
    }
}