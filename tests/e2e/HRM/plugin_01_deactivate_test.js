Feature('DeactivePlugin');
Scenario('@Plugin Deactivating WPERP Plugin', ({ I }) => {
I.amOnPage('https://erpqa.ajaira.website/wp-admin')
I.fillField('Username or Email Address','mehedi')
I.fillField('Password','hoe)6ULjBvW8*C2P#T')
I.checkOption('Remember Me')
I.click('Log In')
I.click('.menu-icon-plugins > .wp-menu-name')
I.click('#deactivate-erp')
I.click('Submit & Deactivate')
I.wait(3)

});
