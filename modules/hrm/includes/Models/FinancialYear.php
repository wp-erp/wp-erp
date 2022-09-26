<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class FinancialYear
 */
class FinancialYear extends Model {
    protected $table = 'erp_hr_financial_years';

    protected $fillable = [
        'fy_name', 'start_date', 'end_date', 'description', 'created_by', 'updated_by',
    ];

    /**
     * Created at date format
     */
    public function setCreatedAtAttribute() {
        $this->attributes['created_at'] = erp_current_datetime()->getTimestamp();
    }

    /**
     * Updated at date format
     */
    public function setUpdatedAtAttribute() {
        $this->attributes['updated_at'] = erp_current_datetime()->getTimestamp();
    }
}
