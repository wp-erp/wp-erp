const helpers = require('../../pages/helpers');
Feature('Sales');

Scenario('@Sales CreateInvoice',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.sales();
        helpers.createInvoice();
});
