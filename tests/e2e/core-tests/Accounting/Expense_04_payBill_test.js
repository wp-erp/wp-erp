const helpers = require('../../pages/helpers');
Feature('Expense');

Scenario('@Expense payBill',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.Expense();
        I.click('//*[@id="erp-accounting"]/div[2]/div[1]/div/div/div/ul/li[4]/a');
        I.click('div.multiselect');
        I.click('//div[3]/ul/li/span/span');
        I.wait(5);
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[4]/div/div[2]');
        I.click('//div[4]/div/div[3]/ul/li/span');
        I.wait(10);
        I.click('//form/div/div/div/div[5]/div/div/div[2]');
        I.click('//div[5]/div/div/div[3]/ul/li/span');
        I.click('Save');
});
