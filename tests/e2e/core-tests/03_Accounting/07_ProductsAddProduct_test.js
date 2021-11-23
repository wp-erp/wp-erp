const helpers = require('../../pages/helpers');
Feature('Products');

Scenario('@Accounting addProducts',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewProducts();
        helpers.addProducts();
        
});





