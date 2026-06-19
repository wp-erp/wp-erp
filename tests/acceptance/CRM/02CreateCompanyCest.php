<?php

// namespace CRM;

class CreateCompanyCest {
    public function _before( AcceptanceTester $I ) {
    }

    // tests
    public function createCompany( Step\Acceptance\AllSteps $I ) {
        $I->loginAsAdmin();
        $I->click( 'WP ERP' );
        $I->click( 'HR' );
        $I->amOnpage( '/wp-admin/admin.php?page=erp-crm&section=companies&section=companies' );
        $I->click( '#erp-company-new' );
        $I->fillField( '#company', randomGenerate1()->company );
        $I->fillField( '#erp-crm-new-contact-email', randomGenerate1()->email );
        $I->fillField( '#contact[main][phone]', randomGenerate1()->email );
        $I->click( '#select2-contactmetalife_stage-container' );
        $I->click( '//span[2]/ul/li[2]' );
        $I->click( '//span[@id="select2-erp-crm-contact-owner-id-container"]' );
        $I->click( '//span[2]/ul/li[2]' );
        $I->click( '//button[contains(text(),"Add New")]' );
    }
}
function randomGenerate1() {
    return \Faker\Factory::create();
}
