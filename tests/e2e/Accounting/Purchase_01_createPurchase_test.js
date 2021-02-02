Feature('Purchase');

Scenario('@Purchase Create purchase', ({ I }) => {
    I.loginAsAdmin();
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/purchases/new');
        I.click('div.multiselect');
        I.click('//th/div/div[2]');
        I.click('//th/div/div[3]/ul/li[2]/span');
        I.wait(5);
        I.click('Save');
        I.seeInCurrentUrl('/wp-admin/admin.php?page=erp-accounting#/purchases');
});
