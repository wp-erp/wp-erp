<?php 
// namespace Scenario;


class AddVendorCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function addVendor(\Step\Acceptance\AllSteps $I)
    {
    	//Add New Vendor
        $I->loginAsAdmin();
        $I->previewUsers();
        $I->click('Vendors');
        $I->wait(5);
        $I->click('Add New Vendor');
        $I->fillField('#first_name', randomGenerateVendor()->firstName);
        $I->fillField('#last_name', randomGenerateVendor()->lastName);
        $I->fillField('#email', randomGenerateVendor()->email);
        $I->fillField('#phone', randomGenerateVendor()->phoneNumber);
        $I->fillField('#company', randomGenerateVendor()->company);
        $I->click('//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');
    }
}

function randomGenerateVendor() {
  return \Faker\Factory::create();
}
