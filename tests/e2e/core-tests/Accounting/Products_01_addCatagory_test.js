const helpers = require('../../pages/helpers');
Feature('Products');

Scenario('@Products addCatagory',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewProducts();
        I.click('Product Categories');
        I.fillField('.wperp-form-field', 'Asset');
        I.click('Save');
});
