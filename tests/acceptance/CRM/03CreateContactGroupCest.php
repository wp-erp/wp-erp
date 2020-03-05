<?php 
// namespace CRM;


class CreateContactGroupCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function createContactGroup(\Step\Acceptance\AllSteps $I)
    {
    	$I->loginAsAdmin();
    	$I->click('WP ERP');
        $I->click('HR');
        $I->amOnpage('/wp-admin/admin.php?page=erp-crm&section=contact-groups');
        $I->click('#erp-new-contact-group');
        $I->fillField('#erp-crm-contact-group-name', 'Basic');
        $I->fillField('#erp-crm-contact-group-description', 'Hers is the contact group description');
        $I->click('//button[contains(text(),"Add New")]');

    }
}
