Feature('Purchase');

Scenario('@Purchase Create purchase order',({ I }) => {
    I.loginAsAdmin();
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/purchase-orders/new');
        I.click('div.multiselect');
        I.wait(5);
        I.click('//div[3]/ul/li/span');
        I.wait(5);
        I.click('//div[@id="erp-accounting"]/div[2]/form/div[2]/div/table/tbody/tr/th/div/div[2]');
        I.click('//th/div/div[3]/ul/li/span/span');
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[2]/div/div/input')
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[2]/div/div/div/div[2]/div/div[3]/div/div/div[4]/div[4]/div/div[2]/div/div')
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[3]/div/div/input')
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[3]/div/div/div/div[2]/div/div[3]/div/div/div[5]/div[4]/div/div[2]/div/div')
        I.click('Save');
        I.dontSee('Please complete these fields:');
        
});
