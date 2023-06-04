const helpers = require('../../pages/helpers');
Feature('New Journal Entry');

Scenario('@Accounting @Journal Add journal entry',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewTransactions();
        helpers.Journal();
             
});
