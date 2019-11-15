<?php
namespace Step\Acceptance;

class AllSteps extends \AcceptanceTester
{
	public function loginAsAdmin()
	{
		$I = $this;
        $I->amOnPage('/wp-admin');
        // $I->click('Log in');
        $I->fillField('#loginform #user_login', 'admin');
        $I->fillField('#loginform #user_pass', 'admin');
        $I->checkOption('rememberme');
        $I->click('Log In');
	}
	public function previewTransactions()
    {
        $I = $this;
        $I->click('WP ERP');
        $I->click('Accounting');
        $I->wait(5);
        $I->moveMouseOver('//div[2]/div/div[2]/div/div/ul/li[3]/a');
    }

}

function randomGenerate() {
  return \Faker\Factory::create();
}
