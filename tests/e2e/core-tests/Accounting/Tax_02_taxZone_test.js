const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Tax addTaxZone',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.Tax();
        helpers.addTaxZone();
});
