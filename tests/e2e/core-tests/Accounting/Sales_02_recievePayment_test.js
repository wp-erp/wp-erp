const helpers = require('../../pages/helpers');
Feature('Sales');

Scenario('@Sales recievePayment',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.sales();
        helpers.recievePayment();
});
