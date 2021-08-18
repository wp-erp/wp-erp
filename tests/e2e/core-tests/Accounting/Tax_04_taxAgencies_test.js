const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Tax addTaxAgencies',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.Tax();
        helpers.addTaxAgencies();
});
