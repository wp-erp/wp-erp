<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Payment extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_payments';
    public $timestamps    = false;
    protected $fillable   = [ 'transaction_id', 'parent', 'child'];

    public function transaction() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Transaction', 'id' );
    }
}