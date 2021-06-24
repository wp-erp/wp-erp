Feature('Expense');

Scenario('@Expense createExpense check',({ I }) => {
    I.loginAsAdmin();
            I.previewTransactions();
            I.click('Expenses');
            I.wait(5);
            I.click('//*[@id="erp-accounting"]/div[2]/div[1]/div/div/div/div')
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

