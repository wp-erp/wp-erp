const helpers = require('../../pages/helpers');
Feature('Deals');
Scenario('Mark as Won',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    helpers.dealsPage();
    /*   Mark as Won Deals   */
    I.click('wedevs deal');
    I.click('Won');
    I.see('wedevs deal');
    I.wait(2);
});
