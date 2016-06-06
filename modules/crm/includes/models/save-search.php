<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class SaveSearch extends Model {
    protected $table = 'erp_crm_save_search';

    protected $fillable = [ 'user_id', 'type', 'global', 'search_name', 'search_val' ];

    public $timestamps = true;
}
