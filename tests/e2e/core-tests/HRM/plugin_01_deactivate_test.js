Feature('DeactivePlugin');

Scenario('@Plugin Deactivating WPERP Plugin',({ I, loginAs}) => {
    loginAs('admin');
    I.click('.menu-icon-plugins > .wp-menu-name');
    I.click('#deactivate-erp');
    I.click('Submit & Deactivate');
    I.wait(3);

});
