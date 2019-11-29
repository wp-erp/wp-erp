<?php
//namespace Scenario;


class SalesCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function salesTransactions(\Step\Acceptance\AllSteps $I)
    {
        $I->loginAsAdmin();
        $I->previewTransactions();
            //Create Invoice
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
            $I->wait(10);

            //Create Receive Payment
            $I->scrollTo('#wpbody-content', 0, 0);
            $I->wait(5);
            $I->click(['css' => '.wperp-selected-option']);
            $I->click('//div[3]/div/div/div/div/ul/li[2]/a');
            $I->wait(5);
            $I->click('//div[@id="erp-accounting"]/div[3]/form/div/div/form/div/div/div/div/div/div[2]');
            $I->click('//div[3]/ul/li/span');
            $I->wait(5);
            $I->click(['xpath' => '//div[@id="erp-accounting"]/div[3]/form/div/div/form/div/div[4]/div/div[2]']);
            $I->click('//div[4]/div/div[3]/ul/li/span');
            $I->click('//form/div/div[5]/div/div/div[2]');
            $I->wait(5);
            $I->click('//div[5]/div/div/div[3]/ul/li/span/span');
            $I->click('//tfoot/tr/td/div/div/div');
            $I->wait(10);  

            //Create Estimate    
            $I->scrollTo('#wpbody-content', 0, 0);
            $I->click(['css' => '.wperp-selected-option']);
            $I->click('Create Estimate');
            $I->wait(5);
            $I->click('//form/div/div/div/div/div/div/div[2]');
            $I->click('//div[3]/ul/li/span');
            $I->click('//th/div/div[2]');
            $I->click('//th/div/div[3]/ul/li/span');
            $I->click('//tfoot/tr/td/div/div/div');

    }
}
