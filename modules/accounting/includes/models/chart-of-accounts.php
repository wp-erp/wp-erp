<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Chart_Of_Accounts extends Model {
    protected $primaryKey = 'id';
    protected $table = 'erp_ac_ledger';
    protected $fillable = [ 'code', 'name', 'type_id', 'cash_account', 'reconcile', 'system', 'active' ];
}