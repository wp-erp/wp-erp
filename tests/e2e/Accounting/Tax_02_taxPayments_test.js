Feature('Tax');

Scenario('@Tax addTaxPayments', ({ I }) => {
    I.loginAsAdmin();
    I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes/tax-records');
    //I.wait(10);
    I.click('New Tax Payment');
    I.click('//div[@class="wperp-col-sm-4 with-multiselect"]//span[@class="multiselect__single"]');
    I.wait(2);
    I.click('//div[3]/ul/li/span');
    I.click('//span[@class="multiselect__placeholder"]');
    I.wait(2);
    I.click('//div[2]/div/div/div[3]/ul/li/span');
    I.click('//div[4]//div[1]//div[1]//div[2]//span[1]');
    I.wait(5);
    I.click('//div[4]/div/div/div[3]/ul/li/span/span');
    I.fillField('//input[@type="number"]', '120');
    I.click('Save');
    I.waitForElement('.app-customers', 30);

});
