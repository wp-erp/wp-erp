var faker = require('faker');

Feature('Customer');

Scenario('add customer',({ I }) => {
    I.loginAsAdmin();
        I.previewUsers();
        I.click('Customers');
        I.wait(5);
        I.click('Add New Customer');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#email', faker.internet.email());
        I.fillField('#phone', faker.phone.phoneNumber());
        I.fillField('#company', faker.company.companyName());
        I.click('//*[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');
        I.waitForElement('.app-customers');
});

