const { helper } = require('codeceptjs');
const helpers = require('../../pages/helpers');
Feature('Vendor');

Scenario('Add vendor',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewUsers();
        helpers.vendor();     
});
