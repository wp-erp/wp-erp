const helpers = require('../../pages/helpers');
Feature('Purchase');

Scenario('@Accounting Create purchase order',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.purchase();
        helpers.createPurchaseOrder();      
});
