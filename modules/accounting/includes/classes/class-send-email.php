<?php
namespace WeDevs\ERP\Accounting\Includes\Classes;

use WeDevs\ERP\Email;


class Send_Email extends Email {

    function __construct() {
        parent::__construct();
    }

    public function trigger( $receiver_emails = [], $subject = 'sub', $body = 'body', $attachement = '' ) {

        $results = [];
        foreach ( $receiver_emails as $email ) {
            $results[] = $this->send( $email, $subject, $body, '', $attachement );
        }

        if ( in_array( false, $results ) ) {
            return false;
        }

        return true;
    }
}
