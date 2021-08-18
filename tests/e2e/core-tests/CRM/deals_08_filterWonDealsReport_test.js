const helpers = require('../../pages/helpers');
Feature('Deals');
Scenario('@Deals Filter Won Deals and View Report',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    /*   Filter   */
    I.click('//*[@id="wpbody-content"]/div[2]/ul/li[3]/a');
    I.click('Won Deals');
    I.wait(3);
    I.scrollPageToBottom();
    I.wait(2);
    I.scrollPageToTop();
    I.wait(2);
    I.click('Close');
    I.wait(2);
    I.click('This month');
    I.click('This week');
    I.click('Open Deals');
    I.click('//div[3]/div/ul/li[2]/a');
    I.see('Won Deals');
});
