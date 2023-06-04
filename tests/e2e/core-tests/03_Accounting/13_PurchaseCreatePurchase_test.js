const helpers = require('../../pages/helpers');
Feature('Purchase');

Scenario('@Accounting @Purchases Create purchase',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.purchase();
        helpers.createPurchase();
});
