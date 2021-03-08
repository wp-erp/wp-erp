var faker = require('faker');

Feature('Contact');

Scenario('Create Contact',({ I }) => {
    I.loginAsAdmin();
    	I.click('WP ERP');
        I.click('HR');
        // I.click('Deprtments');
        I.amOnPage('/wp-admin/admin.php?page=erp-crm&section=contacts');
        I.click('#erp-customer-new');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#erp-crm-new-contact-email', faker.internet.email());
        I.fillField('contact[main][phone]', faker.phone.phoneNumber());
        I.click('#select2-contactmetalife_stage-container');
        // I.wait(5);
        I.click('//span[2]/ul/li[2]');
        I.click('//span[@id="select2-erp-crm-contact-owner-id-container"]');
        // I.wait(5);
        I.click('//span[2]/ul/li[2]');
        I.click('//button[contains(text(),"Add New")]');
        I.waitForElement('#wp-erp', 30);
});
