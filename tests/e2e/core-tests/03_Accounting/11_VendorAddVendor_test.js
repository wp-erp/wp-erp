const { helper } = require('codeceptjs');
const helpers = require('../../pages/helpers');
Feature('Vendor');

Scenario('@Accounting @Purchases Add vendor',({ I, loginAs }) => {
    loginAs('admin');
    // for(x=0; x<=15; x++){
        helpers.accDashboard();
        helpers.previewUsers();
        helpers.vendor();
    // }     
});
