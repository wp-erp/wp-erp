<?php
//namespace Scenario;


class CreateInvoiceCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(\Step\Acceptance\AllSteps $I)
    {
        $I->loginAsAdmin();
        $I->click('WP ERP');
        $I->click('Accounting');
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[3]/a');
        $I->click('Sales');
        $I->wait(5);
        $I->click(['css' => '.wperp-selected-option']);
        $I->click('Create Invoice');
        $I->wait(5);
        $I->click('//form/div/div/div/div/div/div/div[2]');
        $I->click('//div[3]/ul/li/span');
        $I->click('//th/div/div[2]');
        $I->click('//th/div/div[3]/ul/li/span');
        $I->click('//tfoot/tr/td/div/div/div');
    }
}
