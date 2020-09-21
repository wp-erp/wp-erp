<?php

namespace WeDevs\ERP\Admin\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Audit_Log
 */
class Audit_Log extends Model {
    protected $table = 'erp_audit_log';

    protected $fillable = [ 'component', 'sub_component', 'data_id', 'old_value', 'new_value', 'message', 'changetype', 'created_by', 'created_at' ];

    public $timestamps = false;
}
