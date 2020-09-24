<?php

// namespace Scenario;

class AddCustomerCest {
    public function _before( Step\Acceptance\AllSteps $I ) {
        $I->loginAsAdmin();
        $I->previewUsers();
    }

    // tests
    public function addCustomer( Step\Acceptance\AllSteps $I ) {
        //Add new Customer

        $I->click( 'Customers' );
        $I->wait( 5 );
        $I->click( 'Add New Customer' );
        $I->fillField( '#first_name', randomGenerate()->firstName );
        $I->fillField( '#last_name', randomGenerate()->lastName );
        $I->fillField( '#email', randomGenerate()->email );
        $I->fillField( '#phone', randomGenerate()->phoneNumber );
        $I->fillField( '#company', randomGenerate()->company );
        $I->click( '//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]' );
    }

    // // tests
    // public function addVendor(\Step\Acceptance\AllSteps $I)
    // {
    //     //Add New Vendor
    //     // $I->loginAsAdmin();
    //     // $I->previewUsers();
    //     $I->click('Vendors');
    //     $I->wait(5);
    //     $I->click('Add New Vendor');
    //     $I->fillField('#first_name', randomGenerate()->firstName);
    //     $I->fillField('#last_name', randomGenerate()->lastName);
    //     $I->fillField('#email', randomGenerate()->email);
    //     $I->fillField('#phone', randomGenerate()->phoneNumber);
    //     $I->fillField('#company', randomGenerate()->company);
    //     $I->click('//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');
    // }
}

function randomGenerate() {
    return \Faker\Factory::create();
}
