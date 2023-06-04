const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Accounting @Tax addTaxAgencies',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.Tax();
        helpers.addTaxAgencies();
});
