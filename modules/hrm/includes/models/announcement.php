<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Announcement
 */
class Announcement extends Model {
    protected $table    = 'erp_hr_announcement';

    protected $fillable = [ 'user_id', 'post_id', 'status', 'email_status' ];

    public $timestamps  = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'post_id' => 'integer',
    ];
}
