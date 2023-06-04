const helpers = require('../../pages/helpers');
Feature('Products');

Scenario('@Accounting @Config addProducts',({ I, loginAs }) => {
    loginAs('admin');
    // for(x=0; x<=15; x++){
        helpers.accDashboard();
        helpers.previewProducts();
        helpers.addProducts();
    // }
        
});





