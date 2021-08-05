const helpers = require('../../pages/helpers');
Feature('Purchase');

Scenario('@Purchase Create purchase order',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.purchase();
        helpers.createPurchaseOrder();      
});
