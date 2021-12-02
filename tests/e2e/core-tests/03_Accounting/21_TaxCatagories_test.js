const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Accounting addTaxCatagories',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.Tax();
        helpers.addTaxCategory();           
});
