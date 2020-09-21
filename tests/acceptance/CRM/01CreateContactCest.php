<?php

// namespace CRM;

class CreateContactCest {
    public function _before( AcceptanceTester $I ) {
    }

    // tests
    public function createContact( Step\Acceptance\AllSteps $I ) {
        $I->loginAsAdmin();
        $I->click( 'WP ERP' );
        $I->click( 'HR' );
        // $I->click('Deprtments');
        $I->amOnpage( '/wp-admin/admin.php?page=erp-crm&section=contacts' );
        $I->click( '#erp-customer-new' );
        $I->fillField( '#first_name', randomGenerate()->firstName );
        $I->fillField( '#last_name', randomGenerate()->lastName );
        $I->fillField( '#erp-crm-new-contact-email', randomGenerate()->email );
        $I->fillField( '#contact[main][phone]', randomGenerate()->phoneNumber );
        $I->click( '#select2-contactmetalife_stage-container' );
        // $I->wait(5);
        $I->click( '//span[2]/ul/li[2]' );

        $I->click( '//span[@id="select2-erp-crm-contact-owner-id-container"]' );
        // $I->wait(5);
        $I->click( '//span[2]/ul/li[2]' );
        $I->click( '//button[contains(text(),"Add New")]' );
        $I->waitForElement( '#wp-erp', 30 );
    }
}

function randomGenerate() {
    return \Faker\Factory::create();
}
