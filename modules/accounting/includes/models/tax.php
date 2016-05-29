<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Model;

class Tax extends Model {
    protected $primaryKey = 'id';
    protected $table      = 'erp_ac_tax';
    protected $fillable   = ['name', 'tax_number', 'is_compound', 'created_by'];
    public $timestamps = false;

    public function items() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Tax_Items', 'tax_id' );
    }

    public function ledger() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Ledger', 'id', 'account' );
    }

    public function scopeType( $query, $tax_id = 0 ) {
        if ( is_array( $tax_id ) ) {
        	if ( isset( $tax_id['in'] ) ) {
        		return $query->whereIn( 'tax_id', $tax_id['in'] ); 
        	}

        	if ( isset( $tax_id['not_in'] ) ) {
        		return $query->whereNotIn( 'tax_id', $tax_id['not_in'] ); 
        	}
    
        } else {
            return $query->where( 'tax_id', '=', $tax_id );    
        }
    }
}