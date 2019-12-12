<?php 
// namespace Accounting;


class BackendExploratoryCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function backendExploratory(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    	$I->click('WP ERP');
    	$I->click('HR');
    	$I->click('CRM');
    	$I->click('Accounting');
    	$I->click('Company');
    	$I->click('Tools');
    	$I->click('Modules');
    	$I->click('Add-Ons');
    	$I->click('Settings');

    }
}
