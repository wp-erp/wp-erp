const helpers = require('../../pages/helpers');
Feature('Customer');

Scenario('add customer',({ I, loginAs }) => {
    loginAs('admin');
		helpers.accDashboard();
        helpers.previewUsers();
        helpers.addCustomer();      
});

