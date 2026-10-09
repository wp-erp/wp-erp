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
     * Created at, as a unix timestamp.
     *
     * `created_at` / `updated_at` are `int` columns here, not datetimes. Eloquent
     * hands its own `Carbon` instance to these mutators; ignoring the argument is
     * fine, but the value has to be written as a timestamp — a datetime string
     * reaching an `int` column is silently truncated by MySQL to its leading
     * digits, which is how every row ended up with `created_at = 2026`.
     *
     * @param mixed $value Ignored — Eloquent's own timestamp.
     */
    public function setCreatedAtAttribute( $value = null ) {
        unset( $value );

        $this->attributes['created_at'] = erp_current_datetime()->getTimestamp();
    }

    /**
     * Updated at, as a unix timestamp. See `setCreatedAtAttribute()`.
     *
     * @param mixed $value Ignored — Eloquent's own timestamp.
     */
    public function setUpdatedAtAttribute( $value = null ) {
        unset( $value );

        $this->attributes['updated_at'] = erp_current_datetime()->getTimestamp();
    }
}
