<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Dependents
 *
 * @package WeDevs\ERP\HRM\Models
 */
class ContactGroup extends Model {
    protected $table = 'erp_crm_contact_group';

    protected $fillable = [ 'name', 'description' ];

    public $timestamps = true;

    public function contact_subscriber() {
        return $this->hasMany( 'WeDevs\ERP\CRM\Models\ContactSubscriber', 'group_id' );
    }

}