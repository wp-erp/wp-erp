Feature('New Journal Entry');

Scenario('Add journal entry',({ I }) => {
    I.loginAsAdmin();
    	I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/transactions/journals/new');
    	I.wait(5);
        I.click('//td[2]/div/div/div[2]');
        I.wait(5);
        I.click('//li[6]/span/span');
        I.fillField('//td[4]/input', '20000');
        I.click('//tr[2]/td[2]/div/div/div[2]');
        I.click('//tr[2]/td[2]/div/div/div[3]/ul/li[2]/span/span');
        I.fillField('//tr[2]/td[5]/input', '20000');
        I.click('Save');
});
