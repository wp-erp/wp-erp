<?php 
// namespace Scenario\HR;


class CreateDepartmentCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function createDepartment(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    	$I->click('WP ERP');
        $I->click('HR');
        // $I->click('Deprtments');
        $I->amOnpage('/wp-admin/admin.php?page=erp-hr&section=department');
        $I->click('#erp-new-dept');
        $I->wait(5);
        $I->fillField('#dept-title','Support');
        $I->fillField('#dept-desc','Her is listing department name');
        $I->click('#dept-lead');
        $I->click('//span/select/option[2]');
        $I->click('#dept-parent');
        $I->click('//div[4]/span/select/option[2]');
        $I->click('Create Department');
        $I->waitForElementClickable('#erp-new-dept', 30);
        $I->click('#erp-new-dept');
        // $I->wait(5);
        // $I->dontSeeInPopup();
        // $I->dontSee('Multiple department with the same name is not allowed.');
        // $I->waitForElementClickable('#erp-new-dept', 30);
        

    }
}
