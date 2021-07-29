const helpers = require('../../pages/helpers');
Feature('Products');

Scenario('@Products addProducts',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewProducts();
        helpers.addProducts();
        
});





