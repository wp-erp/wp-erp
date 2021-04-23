//const { default: pause } = require("webdriverio/build/commands/browser/pause");
Feature('Tax');

Scenario('@Tax addTaxRate',({ I }) => {
        I.loginAsAdmin();
        I.click('WP ERP');
        I.click('Accounting');
        I.moveCursorTo('//*[@id="erp-accounting"]/div[1]/ul/li[5]/a');
        I.click('Tax Rates');
        I.click('Add Tax Rate');
        I.wait(3);
        I.click('//*[@id="erp-accounting"]/div[2]/div[2]/div/form/div[1]/div[1]/div/div/div[2]');
        I.click('//div[3]/ul/li/span/span'); //
        I.fillField("(//input[@type='text'])[2]", 'Rinky_Automation');
        I.click('//td[2]/div');
        I.click('//td[2]/div/div[3]/ul/li/span');
        I.click("//div[@id='erp-accounting']/div[2]/div[2]/div/form/div[2]/table/tbody/tr/td[3]/div");
        I.click('//td[3]/div');
        I.wait(3);
        I.click('//td[3]/div/div[3]/ul/li[7]/span/span');  
        I.click('//*[@id="erp-accounting"]/div[2]/div[2]/div/form/div[2]/table/tbody/tr/td[4]/input');
        I.type('200');
        I.click('Save');
        I.wait(5);
        
});


