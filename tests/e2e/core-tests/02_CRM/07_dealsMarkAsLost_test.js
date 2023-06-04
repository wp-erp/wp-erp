const helpers = require('../../pages/helpers');
Feature('Deals');
Scenario('@CRM @Deals Mark as Lost',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    helpers.dealsPage();
    /*   Mark as Lost Deals   */
    I.click('wedevs deal');
    I.click('Lost');
    I.click('//*[@id="lost-reason-modal-body"]/div[1]/div/div[2]/span');
    I.pressKey("ArrowDown");
    I.pressKey("ArrowDown");
    I.pressKey("Enter");
    I.wait(2);
    I.click('//*[@id="lost-reason-modal-body"]/div[2]/textarea');
    I.type('test purpose');
    I.click('Mark as Lost');
    I.wait(2);
});
