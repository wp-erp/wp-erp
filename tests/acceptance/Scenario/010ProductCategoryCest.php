<?php 
// namespace Accounting;


class ProductCategoryCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function productCategory(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    	$I->click('WP ERP');
        $I->click('Accounting');
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[6]/a');
        $I->click('Product Categories');
        $I->wait(5);
        // $I->waitForElement('.app-taxes', 30);
    	// $I->click('Product Categories');
    	$I->fillField('.wperp-form-field', 'Sports');
    	$I->click('Save');
    }
}
