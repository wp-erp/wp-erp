<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class Campaign extends Model {
    protected $table = 'erp_crm_campaigns';

    protected $fillable = [ 'title', 'description' ];

    public $timestamps = true;

    /**
     * Set pivot relation with erp_crm_campign_group table
     *
     * @since 1.0
     *
     * @return [type] [description]
     */
    public function groups() {
        return $this->belongsToMany( '\WeDevs\ERP\CRM\Models\ContactGroup', $this->getConnection()->db->prefix . 'erp_crm_campaign_group', 'campaign_id', 'group_id' );
    }

}