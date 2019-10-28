<?php
//namespace Scenario;


class AdminCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/wp-admin');
        // $I->click('Log in');
        $I->fillField('#loginform #user_login', 'admin');
        $I->fillField('#loginform #user_pass', 'admin');
        $I->checkOption('rememberme');
        $I->click('Log In');
        $I->click('WP ERP');
        $I->click('Accounting');
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[3]/a');
        $I->click('Sales');
        $I->wait(5);
//        $I->click('//a[contains(text(),\"Transactions\")][2]');
//        $I->moveMouseOver('New Transaction');
//        $I->click('//div[2]/div/div[2]/div/div/ul/li[3]/a','New Transaction');
        $I->click(['css' => '.wperp-selected-option']);

        $I->click('Create Invoice');
        $I->wait(5);
        $I->click('//form/div/div/div/div/div/div/div[2]');
//    $I->click(['css' => '.multiselect--active > .multiselect__tags']);
        $I->click('//div[3]/ul/li/span');
        $I->click('//th/div/div[2]');
        $I->click('//th/div/div[3]/ul/li/span');
        $I->click('//tfoot/tr/td/div/div/div');
    }
}
