<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Ledger extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_ledger';
    public $timestamps    = false;
    protected $fillable   = [ 'code', 'name', 'description', 'type_id', 'currency', 'tax', 'cash_account', 'reconcile', 'system', 'active', 'created_by'];

    public function scopeBank( $query ) {
        return $query->where( 'cash_account', '=', 1 );
    }

    public function scopeActive( $query ) {
        return $query->where( 'active', 1 );
    }

    public function scopeCode( $query, $code = '' ) {
        return $query->where( 'code', $code );
    }

    public function bank_details() {
        return $this->hasOne( 'WeDevs\ERP\Accounting\Model\Bank', 'ledger_id', 'id' );
    }

    public function journals() {
        return $this->hasMany( '\WeDevs\ERP\Accounting\Model\Journal', 'ledger_id' );
    }

    public function charts() {
        return $this->hasOne( '\WeDevs\ERP\Accounting\Model\Chart_Type', 'id', 'type_id' );
    }

}