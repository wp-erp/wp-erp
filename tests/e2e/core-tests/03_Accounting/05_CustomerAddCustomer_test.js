const helpers = require('../../pages/helpers');
Feature('Customer');

Scenario('@Accounting @Config Add customer',({ I, loginAs }) => {
    loginAs('admin');
    // for(x=0; x<=15; x++){
      helpers.accDashboard();
      helpers.previewUsers();
      helpers.addCustomer();      
    // }
});

