const helpers = require('../../pages/helpers');
Feature('Purchase');

Scenario('@Accounting payPurchase',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.purchase();
        helpers.payPurchase();
});
