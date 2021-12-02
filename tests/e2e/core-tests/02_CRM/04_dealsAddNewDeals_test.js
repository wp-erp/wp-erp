const helpers = require('../../pages/helpers');
Feature('Deals');
Scenario('Add new Deals',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    helpers.dealsPage();
    /*   Adding new Deals   */
    I.wait(4);
    I.click('//*[@id="erp-deals"]/div/div[1]/div[1]/h1/a');
    I.wait(4);
    I.click('//*[@id="new-deal-modal-body"]/div[1]/div/div[2]/input');
    I.type('auto');
    I.wait(4);
    I.pressKey("ArrowDown");
    I.pressKey("Enter");
    I.wait(2);
    I.click('//*[@id="new-deal-modal-body"]/div[2]/div/div[2]/input');
    I.type('wedevs');
    I.wait(4);
    I.pressKey("ArrowDown");
    I.pressKey("Enter");
    I.wait(2);
    I.click('//*[@id="new-deal-modal-body"]/div[4]/input');
    I.type('1500');
    I.click('//*[@id="new-deal-modal-body"]/div[5]/div/ul/li[2]');
    I.click('Save');
    I.click('OK');
});
