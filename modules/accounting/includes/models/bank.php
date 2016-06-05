<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Bank extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_banks';
    public $timestamps    = false;
    protected $fillable   = [ 'ledger_id', 'account_number', 'bank_name' ];

}