//const { default: pause } = require("webdriverio/build/commands/browser/pause");

Feature('Tax');

Scenario('@Tax addTaxZone',({ I }) => {
    I.loginAsAdmin();
        I.click('WP ERP');
        I.click('Accounting');
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/settings/taxes/rate-names');
        I.click('Add Tax Zone');
        I.fillField('//*[@id="wperp-tax-agency-modal"]/div/div/form/div[1]/div[1]/input', 'Noakhali');
        I.fillField('//*[@id="wperp-tax-agency-modal"]/div/div/form/div[1]/div[2]/input','12345')
        I.click('Save')

});
