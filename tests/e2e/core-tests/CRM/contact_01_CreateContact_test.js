var faker = require('faker');

Feature('Contact');

Scenario('Create Contact',({ I }) => {
     I.loginAsAdmin();
        I.click('WP ERP');
        I.click('CRM');
        I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]/a');
        I.click('#erp-customer-new');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#erp-crm-new-contact-email', faker.internet.email());
        I.fillField('contact[main][phone]', faker.phone.phoneNumber());
        I.click('#select2-contactmetalife_stage-container');
        I.click('//span[2]/ul/li[2]');
        I.click('//span[@id="select2-erp-crm-contact-owner-id-container"]');
        I.click('//span[2]/ul/li[2]');
        I.click('//button[contains(text(),"Add New")]');
        I.waitForElement('#wp-erp', 30);
});
