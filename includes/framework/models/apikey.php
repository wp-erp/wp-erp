<?php

namespace WeDevs\ERP\Framework\Models;

use WeDevs\ERP\Framework\Model;

class APIKey extends Model {
    protected $table    = 'erp_api_keys';

    public $timestamps  = false;

    protected $fillable = [ 'name', 'api_key', 'api_secret', 'user_id', 'last_accessed_at', 'created_at' ];
}
