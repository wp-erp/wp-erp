var faker = require('faker');
Feature('Expense');

Scenario('@Expense createExpense',({ I }) => {
    I.loginAsAdmin();
        I.previewTransactions();
            I.click('Expenses');
            I.wait(5);
            I.click('//*[@id="erp-accounting"]/div[2]/div[1]/div/div/div/div')
            //I.moveCursorTo('#erp-accounting');
            //I.click({ css : '.wperp-selected-option'});
            I.click('Create Expense');
            I.wait(5);
            I.click('//form/div/div/div/div/div/div[2]');
            I.click('//div[3]/ul/li/span');
            I.wait(5);
            I.click('//form/div/div[4]/div');
            I.click('//div[4]/div/div[3]/ul/li/span');
            I.wait(5);
            I.click('//form/div/div[5]/div/div');
            I.click('//div[5]/div/div/div[3]/ul/li/span');
            I.click('//td[2]/div/div[2]');
            I.click('//td[2]/div/div[3]/ul/li/span/span');
            I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/form/div/div[3]/div/div/input')
            I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/form/div/div[3]/div/div/div/div[2]/div/div[3]/div/div/div[3]/div[2]/div/div[2]/div')
            I.fillField('//td[4]/input', 40);
            I.click('//tfoot/tr/td/div/div/div');
            I.wait(5);
            I.seeInCurrentUrl('/wp-admin/admin.php?page=erp-accounting#/transactions/expenses/');
});
