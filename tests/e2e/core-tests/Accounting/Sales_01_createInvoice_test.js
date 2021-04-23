Feature('Sales');

Scenario('@Sales CreateInvoice',({ I }) => {
    I.loginAsAdmin();
        I.previewTransactions();
        I.click('Sales');
        I.wait(5);
        I.click({css : '.wperp-selected-option'});
        I.click('Create Invoice');
        I.wait(4);
        I.click('//form/div/div/div/div/div/div/div[2]');
        I.click('//div[3]/ul/li/span');
        I.click('//th/div/div[2]');
        I.click('//th/div/div[3]/ul/li/span');
        I.click('//tfoot/tr/td/div/div/div');
});
