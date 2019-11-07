<?php
//namespace Scenario;


class ReceivePaymentCest
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
        $I->click('//div[3]/div/div/div/div/ul/li[2]/a');
        $I->wait(5);

        $I->click('//div[@id="erp-accounting"]/div[3]/form/div/div/form/div/div/div/div/div/div[2]');
        $I->click('//div[3]/ul/li/span');

        $I->click('//form/div/div[4]/div/div[2]');
        $I->click('//div[4]/div/div[3]/ul/li/span');

        $I->click('//form/div/div[5]/div/div/div[2]');
        $I->wait(5);
        $I->click('//div[5]/div/div/div[3]/ul/li/span/span');

        $I->click('//tfoot/tr/td/div/div/div');
        $I->wait(10);
        //div[5]/div/div/div[3]/ul/li/span/span
        //div[@id='erp-accounting']/div[3]/form/div/div/form/div/div[5]/div/div/div[3]/ul/li/span/span

//
//        $I->click('//form/div/div[4]/div/div[2]');
//        $I->click('//div[4]/div/div[3]/ul/li/span');
//        $I->click('//div[4]/div/div[3]/ul/li/span/span');
//        $I->click('//div[5]/div/div/div[3]/ul/li/span/span');
//        $I->click('//tfoot/tr/td/div/div/div');
    }
}
