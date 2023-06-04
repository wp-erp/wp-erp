const helpers = require('../../pages/helpers');
Feature('Deals');
Scenario('@CRM @Deals Filter new Deals and View Report',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
     /*   Filter   */   
    I.click('//*[@id="wpbody-content"]/div[2]/ul/li[3]/a');
    I.click('New Deals');
    I.wait(6);
    I.scrollPageToBottom();
    I.click('Close');
    I.wait(4);
    I.scrollPageToTop();
    I.click('This month');
    I.wait(2);
    I.click('This week');
    I.click('Open Deals');
    I.wait(2);
    I.click('Open Deals');
    I.see('Open Deals');    
});
