const helpers = require('../../pages/helpers');
Feature('Products');

Scenario('@Accounting @Config addCatagory',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewProducts();
        // I.click('Product Categories');
        I.click('#erp-act-menu-products > ul > li:nth-child(2) > a');
        I.fillField('.wperp-form-field', 'Asset');
        I.click('Save');
});
