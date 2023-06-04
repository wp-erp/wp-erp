const helpers = require('../../pages/helpers');
Feature('Deals');
Scenario('@CRM @Deals Filter Lost Deals and View Report',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    /*   Filter   */
    I.click('//*[@id="wpbody-content"]/div[2]/ul/li[3]/a');
    I.click('Lost Deals');
    I.wait(3);
    I.scrollPageToBottom();
    I.wait(2);
    I.scrollPageToTop();
    I.wait(2);
    I.click('Close');
    I.wait(2);
    I.click('This month');
    I.click('This week');
    I.click('Lost Deals');
    I.see('Lost Deals');
});
