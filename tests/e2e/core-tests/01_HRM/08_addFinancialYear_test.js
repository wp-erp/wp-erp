const helpers = require('../../pages/helpers');
Feature('Financial Year');

Scenario('@HRM @Leave Add financial year', ({ I, loginAs}) => {
    loginAs('admin');
        I.click('WP ERP');
        I.click('Settings');
        I.click('//*[@id="erp-settings"]//li[2]/li/a');
        I.amOnPage('/wp-admin/admin.php?page=erp-settings#/erp-hr/financial');
        I.click('//*[@id="erp-settings-box-erp-hr-financial"]/form/div[2]/div[1]/input');
        I.type('2021');
        I.click('//*[@id="erp-settings-box-erp-hr-financial"]/form/div[2]/div[2]');
        I.type('2021-01-01');
        I.click('//*[@id="erp-settings-box-erp-hr-financial"]/form/div[2]/div[3]');
        I.type('2021-12-31');
        I.click('.wperp-btn');
    
});
