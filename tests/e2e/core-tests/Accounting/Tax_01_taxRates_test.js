const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Tax addTaxRate',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.Tax();
        helpers.addTaxRate();
});