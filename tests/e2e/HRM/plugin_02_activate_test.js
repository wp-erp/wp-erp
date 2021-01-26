Feature('ActivePlugin');
Scenario('@Plugin Activating WPERP Plugin', ({ I }) => {
I.amOnPage('https://erpqa.ajaira.website/wp-admin')
I.fillField('Username or Email Address','mehedi')
I.fillField('Password','hoe)6ULjBvW8*C2P#T')
I.checkOption('Remember Me')
I.click('Log In')
I.click('.menu-icon-plugins > .wp-menu-name')
I.click('#activate-erp')
});
