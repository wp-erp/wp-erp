<?php 
// namespace Scenario;


class PayBillCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function payBill(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
     //        $I->previewTransactions();
     //        $I->click('Expenses');
     //        $I->wait(5);
     //        $I->moveMouseOver('#erp-accounting');
     //        $I->click(['css' => '.wperp-selected-option']);
     //        $I->click('//td/div/div/div');
     //        $I->wait(5);
    	$I->amOnPage('/wp-admin/admin.php?page=erp-accounting#/pay-bills/new');
        $I->wait(5);
        $I->click('div.multiselect');
        $I->click('//div[3]/ul/li/span/span');
        $I->wait(5);
        $I->click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[4]/div/div[2]');
        $I->click('//div[4]/div/div[3]/ul/li/span');
        $I->wait(10);
        $I->click('//form/div/div/div/div[5]/div/div/div[2]');
        $I->click('//div[5]/div/div/div[3]/ul/li/span');
        $I->click('Save');
    }
}
