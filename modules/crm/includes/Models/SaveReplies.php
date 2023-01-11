<?php

namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 */
class SaveReplies extends Model {
    protected $table = 'erp_crm_save_email_replies';

    protected $fillable = [ 'name', 'subject', 'template' ];

    public $timestamps = false;
}
