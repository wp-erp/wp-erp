<?php
/**
 * Delete all the entitlements those created for inactive employees
 * Version 1.3.5 updated
 *
 *
 * @return void
 */
function wperp_update_remove_entitlements_1_3_5() {
    // New Contact Owned
    $new_contact_owned = [
        'subject' => 'New contact has been owned to you',
        'heading' => 'New Contact Owned',
        'body'    => 'Hello {contact_name},

You own a new contact now .<strong>{employee_name}</strong> has been assigned to you by {created_by}.

Regards
Manager Name
Company'
    ];

    update_option( 'erp_email_settings_new-contact-owned', $new_contact_owned );
}

wperp_update_remove_entitlements_1_3_5();
