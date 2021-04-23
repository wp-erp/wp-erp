var faker = require('faker');
Feature('Contact Group');

Scenario('Create Contact Group',({ I }) => {
    I.loginAsAdmin();
    	I.click('WP ERP');
        I.click('CRM');
        I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]/a');
        I.click('Contact Groups');
        I.click('#erp-new-contact-group');
        I.fillField('#erp-crm-contact-group-name', 'Basic');
        I.fillField('#erp-crm-contact-group-description', 'Hers is the contact group description');
        I.click('//button[contains(text(),"Add New")]');
});
