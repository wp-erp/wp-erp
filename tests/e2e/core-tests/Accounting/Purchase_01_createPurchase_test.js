const helpers = require('../../pages/helpers');
Feature('Purchase');

Scenario('@Purchase Create purchase',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.purchase();
        helpers.createPurchase();
});
