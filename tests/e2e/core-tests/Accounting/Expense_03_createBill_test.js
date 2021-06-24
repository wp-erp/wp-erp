Feature('Expense');

Scenario('@Expense createBill',({ I }) => {
    I.loginAsAdmin();
            I.previewTransactions();
            I.click('Expenses');
            I.wait(5);
            I.click('//*[@id="erp-accounting"]/div[2]/div[1]/div/div/div/div')
            I.click('Create Bill');
            I.wait(5);
            I.click('//form/div/div/div/div/div/div[2]');
            I.click('//div[3]/ul/li/span/span');
            I.click('//td[2]/div/div[2]');
            I.click('//td[2]/div/div[3]/ul/li/span/span');
            I.fillField('//td[4]/input', '100');
            I.click('//div/button');
            I.waitForElement('.table-container', 30); // secs
});
