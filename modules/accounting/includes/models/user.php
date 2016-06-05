<?php
namespace WeDevs\ERP\Accounting\Model;

use WeDevs\ERP\Framework\Models\People;

class User extends People {
    
    public function transactions() {
        return $this->hasMany( 'WeDevs\ERP\Accounting\Model\Transaction', 'id', 'user_id' );
    }
}