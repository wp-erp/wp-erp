const helpers = require('../../pages/helpers');
Feature('Expense');

Scenario('@Accounting createCheck',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.Expense();
        I.click('Create Check');
        I.wait(5);
        I.click({css : 'div.multiselect__tags'});
        I.click('//div[3]/ul/li/span');
        I.fillField('//div[2]/div/input','823476347673');
        I.click('//div[@id="erp-accounting"]/div[2]/form/div/div/div/div[4]/div/div[2]');
        I.click('//div[4]/div/div[3]/ul/li/span');
        I.click('//td[2]/div/div[2]');
        I.click('//td[2]/div/div[3]/ul/li/span/span');
        I.fillField('//td[4]/input', 600);
        I.click('Save');
        I.waitForElement('#erp-accounting', 30);
});

