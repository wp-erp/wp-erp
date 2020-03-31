<?php 
// namespace Scenario;


class AddProductCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function addProduct(\Step\Acceptance\AllSteps $I)
    {
    	//Add Products
        $I->loginAsAdmin();
        $I->previewProducts();
        $I->click('Products & Services');
        $I->wait(5);
        $I->click('Add New Product');
        $I->fillField('//div[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div/div[2]/input', 'Phone Charger');
        $I->fillField('#cost-price','150');
        $I->fillField('#sale-price','220');
        $I->click('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[4]/div[2]/div/div[2]/div/div/div[2]/span');
        $I->wait(5);
        $I->click('//div[4]/div[2]/div/div[2]/div/div/div[3]/ul/li/span/span');
        // $I->click('//form/div[2]/div');
        $I->click('Save');
    }
}
