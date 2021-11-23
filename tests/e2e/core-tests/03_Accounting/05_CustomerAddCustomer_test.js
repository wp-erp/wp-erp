const helpers = require('../../pages/helpers');
Feature('Customer');

Scenario('@Accounting Add customer',({ I, loginAs }) => {
    loginAs('admin');
      helpers.accDashboard();
      helpers.previewUsers();
      helpers.addCustomer();      
});

