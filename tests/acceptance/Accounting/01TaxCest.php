<?php 
// namespace Accounting;


class TaxCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tax(\Step\Acceptance\AllSteps $I)
    {	
    	$I->loginAsAdmin();
    	// $I->click('WP ERP');
     //    $I->click('Accounting');
     //    $I->wait(5);
     //    $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[7]/a');
     //    $I->click('Tax Rates');
     //    $I->waitForElement('.app-taxes',30);
    	$I->amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes/rate-names');
    	$I->wait(5);
        $I->click('//div[2]/div[2]/div/ul/li/a');
        $I->seeLink('.wperp-col','Add Tax Rate');
        $I->wait(5);
        $I->click('.wperp-col','Add Tax Rate');

        $I->acceptPopup();
        $I->fillField('.wperp-form-field','Dhaka');
        $I->fillField('.wperp-form-field','74368');
        $I->click('Save');
       
    }
}
