<?php 
// namespace Scenario;
use Codeception\Util\Locator;

class BasicAccoutingCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function basicAccounting(\Step\Acceptance\AllSteps $I)
    {
    	//Add new Customer
    	$I->loginAsAdmin();
        $I->click('WP ERP');
        $I->click('Accounting');
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[2]/a');
        $I->click('Customers');
        $I->wait(5);
        $I->click('Add New Customer');
        $I->fillField('#first_name', randomGenerate()->firstName);
        $I->fillField('#last_name', randomGenerate()->lastName);
        $I->fillField('#email', randomGenerate()->email);
        $I->fillField('#phone', randomGenerate()->phoneNumber);
        $I->fillField('#company', randomGenerate()->company);
        $I->click('//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');

        // //Add New Vendor
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[2]/a');
        $I->click('Vendors');
        $I->wait(5);
        $I->click('Add New Vendor');
        $I->fillField('#first_name', randomGenerate()->firstName);
        $I->fillField('#last_name', randomGenerate()->lastName);
        $I->fillField('#email', randomGenerate()->email);
        $I->fillField('#phone', randomGenerate()->phoneNumber);
        $I->fillField('#company', randomGenerate()->company);
        $I->click('//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');

        //Add Products
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[6]/a');
        $I->click('Products & Services');
        $I->wait(5);
        $I->click('Add New Product');
        $I->fillField('//div[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div/div[2]/input', 'Headphone');
        $I->fillField('#cost-price','200');
        $I->fillField('#sale-price','250');
        $I->click('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[4]/div[2]/div/div[2]/div/div/div[2]/span');
        $I->wait(5);
        $I->click('//div[4]/div[2]/div/div[2]/div/div/div[3]/ul/li/span/span');
        // $I->click('//form/div[2]/div');
        $I->click('Save');
    }
}

function randomGenerate() {
  return \Faker\Factory::create();
}
