const helpers = require('../../pages/helpers');
Feature('Designation');
Scenario('addDesignation', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addDesignation();
});
