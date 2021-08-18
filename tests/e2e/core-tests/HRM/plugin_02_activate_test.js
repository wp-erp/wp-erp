Feature('ActivePlugin');

Scenario('@Plugin Activating WPERP Plugin',({ I, loginAs}) => {
    loginAs('admin');
    I.click('.menu-icon-plugins > .wp-menu-name');
    I.click('#activate-erp');
    });
