<?php 
// namespace Scenario;


class CreatePurchaseOrderCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function createPurchaseOrder(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    //         $I->previewTransactions();
     //        $I->click('Purchase');
     //        $I->wait(5);
     //        $I->moveMouseOver('#erp-accounting');
     //        $I->click(['css' => '.wperp-selected-option']);
     //        $I->click('Create Purchase Order');
     //        $I->wait(5);
        $I->amOnPage('/wp-admin/admin.php?page=erp-accounting#/purchase-orders/new');
        $I->click('div.multiselect');
        $I->wait(5);
        $I->click('//div[3]/ul/li/span');
        $I->wait(5);
        $I->click('//div[@id="erp-accounting"]/div[2]/form/div[2]/div/table/tbody/tr/th/div/div[2]');
        $I->click('//th/div/div[3]/ul/li/span/span');
        $I->click('Save');
        $I->dontSee('Please complete these fields:');
        // $I->wait(10);
    }
}
