Feature('Sales');

Scenario('@Sales recievePayment', ({ I }) => {
    I.loginAsAdmin();
    I.previewTransactions();
    I.click('Sales');
    I.wait(5);
    I.click({css : '.wperp-selected-option'});
    I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/payments/new');
    I.wait(5);
    I.click('//form/div/div/div/div/div/div[2]'); 
    // I.click('//div[@id="erp-accounting"]/div[3]/form/div/div/form/div/div/div/div/div/div[2]');
    I.click('//div[3]/ul/li/span');
    // I.wait(5);
    I.click('//form/div/div[4]/div/div[2]');
    I.click('//div[4]/div/div[3]/ul/li/span');
    I.click('//form/div/div[5]/div/div/div[2]');
    // I.wait(5);
    I.click('//div[5]/div/div/div[3]/ul/li/span/span');
    I.click('//tfoot/tr/td/div/div/div');
    // I.wait(10);
});
