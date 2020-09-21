<?php

// namespace Scenario;

class NewJournalEntryCest {
    public function _before( AcceptanceTester $I ) {
    }

    // tests
    public function newJournalEntry( Step\Acceptance\AllSteps $I ) {
        $I->loginAsAdmin();
        // $I->previewTransactions();
        //       $I->click('Journals');
        //       $I->wait(5);
        //       $I->click('New Journal Entry');
        //       $I->wait(5);
        //       $I->see('New Journal');
        $I->amOnPage( '/wp-admin/admin.php?page=erp-accounting#/transactions/journals/new' );
        $I->wait( 5 );
        $I->click( '//td[2]/div/div/div[2]' );
        $I->wait( 5 );
        $I->click( '//li[6]/span/span' );
        $I->fillField( '//td[4]/input', '20000' );
        // $I->wait(5);
        // $I->click('//tr[2]/td[2]/div/div/div[2]/input');
        // $I->fillField('Revenue');
        // $I->click('//tr[2]/td[5]/input');
        // $I->fillField('//tr[2]/td[2]/div/div/div[2]/input','10000');
        // $I->click('Save');
        $I->click( '//tr[2]/td[2]/div/div/div[2]' );
        $I->click( '//tr[2]/td[2]/div/div/div[3]/ul/li[2]/span/span' );
        $I->fillField( '//tr[2]/td[5]/input', '20000' );
        $I->click( 'Save' );
    }
}
