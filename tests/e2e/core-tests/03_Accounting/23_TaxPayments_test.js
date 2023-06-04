const helpers = require('../../pages/helpers');
Feature('Tax');

Scenario('@Accounting @Tax addTaxPayments',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewSettings();
        helpers.taxPayment();    
});
