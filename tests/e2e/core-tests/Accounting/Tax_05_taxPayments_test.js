const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Tax addTaxPayments',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.taxPayment();    
});
