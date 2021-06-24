const helpers = require('../../pages/helpers');
Feature('Designation');
Scenario('addDepartment', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addDesignation();
});
