<?php
namespace WeDevs\ERP\CRM\Models;

use WeDevs\ERP\Framework\Model;
use WeDevs\ERP\Framework\Models\People;

class Contact extends People {

    public function tags(){
        global $wpdb;
       return $this->belongsToMany('WeDevs\ERP\CRM\Models\CRMTag', "{$wpdb->prefix}erp_crm_contact_tag", 'contact_id', 'tag_id')->withTimestamps();
    }

}
