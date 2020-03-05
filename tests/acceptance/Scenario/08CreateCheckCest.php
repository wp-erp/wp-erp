<?php 
// namespace Accouting;


class CreateCheckCest
{
    public function _before(AcceptanceTester $I)
    {
    }

     public function createCheck(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    		$I->previewTransactions();
            $I->click('Expenses');
            $I->wait(5);
            $I->moveMouseOver('#erp-accounting');
            $I->click(['css' => '.wperp-selected-option']);
            $I->click('Create Check');
            $I->wait(5);
    	// $I->amOnPage('/wp-admin/admin.php?page=erp-accounting#/checks/new');
    	// $I->wait(5);
    	$I->click(['css' => 'div.multiselect__tags']);
    	$I->click('//div[3]/ul/li/span');
    	$I->fillField('//div[2]/div/input','823476347673');
    	$I->click('//div[@id="erp-accounting"]/div[2]/form/div/div/div/div[4]/div/div[2]');
    	$I->click('//div[4]/div/div[3]/ul/li/span');
    	$I->click('//td[2]/div/div[2]');
    	$I->click('//td[2]/div/div[3]/ul/li/span/span');
    	$I->fillField('//td[4]/input', 600);
    	$I->click('Save');
    	$I->waitForElement('#erp-accounting', 30);
    }
}
