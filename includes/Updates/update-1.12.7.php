<?php

use WeDevs\ERP\WeDevsERPInstaller;

/**
 * Class to handle updates for version 1.11.0
 *
 * @since 1.11.0
 */
class ERP_Update_1_12_7 {

    /**
     * Class constructor.
     *
     * @since 1.11.0
     */
    public function __construct() {
        $mailer = new WeDevsERPInstaller();
        $mailer->setup_default_emails();
    }
}

new ERP_Update_1_12_7();
