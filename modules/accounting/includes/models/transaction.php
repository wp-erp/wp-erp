<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Transaction extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_transactions';
    public $timestamps    = false;
    protected $fillable   = [ 'type', 'form_type', 'status', 'user_id', 'billing_address', 'ref', 'summary', 'issue_date', 'due_date', 'currency', 'conversion_rate', 'sub_total', 'total', 'due', 'trans_total', 'invoice_number', 'invoice_format', 'files', 'created_by', 'created_at'];

    public function items() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Transaction_Items', 'transaction_id' );
    }

    public function journals() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Journal', 'transaction_id' );
    }

    public function payments() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Payment', 'transaction_id' );
    }

    public function user() {
        return $this->hasOne( 'WeDevs\ERP\Accounting\Model\User', 'id', 'user_id' );
    }

    public function scopeType( $query, $type = 'expense' ) {
        
        if ( is_array( $type ) ) {
            return $query->whereIn( 'type', $type );    
        } else {
            return $query->where( 'type', '=', $type );    
        }
    }

    public function scopeOfUser( $query, $user_id = 0 ) {
        return $query->where( 'user_id', '=', $user_id );
    }
}