<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Journal extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_journals';
    public $timestamps    = false;
    protected $fillable   = [ 'ledger_id', 'transaction_id', 'type', 'debit', 'credit' ];

    public function ledger() {
        return $this->hasOne( 'WeDevs\ERP\Accounting\Model\Ledger', 'id', 'ledger_id' );
    }

    public function transaction() {
        return $this->hasOne( 'WeDevs\ERP\Accounting\Model\Transaction', 'id', 'transaction_id' );
    }

    public function items() {
        return $this->hasOne( 'WeDevs\ERP\Accounting\Model\Transaction_Items', 'tax_journal', 'id' );
    }

    public function scopeofLedger( $query, $ledger_id = '' ) {
        return $query->where( 'ledger_id', $ledger_id );
    }
}
