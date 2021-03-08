var faker = require('faker');

Feature('Vendor');

Scenario('Add vendor',({ I }) => {
    I.loginAsAdmin();
        I.previewUsers();
        I.click('Vendors');
        I.wait(5);
        I.click('Add New Vendor');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#email', faker.internet.email());
        I.fillField('#phone', faker.phone.phoneNumber());
        I.fillField('#company', faker.company.companyName());
        I.click('//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');
        I.waitForElement('.app-customers');

});
