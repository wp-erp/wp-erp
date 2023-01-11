<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * HR Email handler class
 */
class Emailer {
    use Hooker;

    public function __construct() {
        $this->filter( 'erp_email_classes', 'register_emails' );
    }

    public function register_emails( $emails ) {
        $emails['NewEmployeeWelcome']     = new Emails\NewEmployeeWelcome();
        $emails['NewLeaveRequest']        = new Emails\NewLeaveRequest();
        $emails['ApprovedLeaveRequest']   = new Emails\ApprovedLeaveRequest();
        $emails['RejectedLeaveRequest']   = new Emails\RejectedLeaveRequest();
        $emails['BirthdayWish']            = new Emails\BirthdayWish();
        $emails['HiringAnniversaryWish']  = new Emails\HiringAnniversaryWish();
        $emails['GovtHolidayReminder']    = new Emails\GovtHolidayReminder();

        return apply_filters( 'erp_hr_email_classes', $emails );
    }
}
