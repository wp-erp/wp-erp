var faker = require('faker');
Feature('Company');

Scenario('Create a company', ({ I }) => {
    I.loginAsAdmin();
    	I.click('WP ERP');
        I.click('HR');
        I.amOnPage('/wp-admin/admin.php?page=erp-crm&section=companies&section=companies');
        I.click('#erp-company-new');
        I.fillField('#company', faker.company.companyName());
        I.fillField('#erp-crm-new-contact-email', faker.internet.email());
        I.fillField('contact[main][phone]', faker.phone.phoneNumber());
        I.click('#select2-contactmetalife_stage-container');
        I.click('//span[2]/ul/li[2]');
        I.click('//span[@id="select2-erp-crm-contact-owner-id-container"]');
        I.click('//span[2]/ul/li[2]');
        I.click('//button[contains(text(),"Add New")]');
});
