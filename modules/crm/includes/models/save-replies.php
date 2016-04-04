<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Save_Replies extends Model {
    protected $table = 'erp_crm_save_email_replies';

    protected $fillable = [ 'name', 'subject', 'template' ];

    public $timestamps = false;

}
