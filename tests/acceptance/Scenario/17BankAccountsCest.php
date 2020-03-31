<?php 
// namespace Scenario;


class BankAccountsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function transferMoney(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    	$I->amOnPage('/wp-admin/admin.php?page=erp-accounting#/banks');
    	$I->wait('5');
    	$I->click(['css' => '.wperp-selected-option']);
    	$I->click('Transfer Money');
    	$I->wait(5);
    	// $I->click('//div[2]/div/div/div/a','Add new');
    	$I->click(['css' => '.wperp-btn']);
    	// $I->wait(5);
    	$I->click('//div[@id="transfer_funds_from"]//span[@class="multiselect__single"]');
    	$I->wait(5);
    	$I->click('//div[3]/ul/li/span');
    	$I->click('//div[3]/ul/li/span');

    	$I->click('//div[@id="transfer_funds_to"]//span[@class="multiselect__single"]');
    	$I->click('//div[2]/div/div/div[3]/ul/li/span');
    	$I->fillField('#transfer_amount', '100');
    	$I->click('//div[2]/button');

    }
}
