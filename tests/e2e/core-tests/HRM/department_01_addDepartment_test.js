const helpers = require('../../pages/helpers');
Feature('Department');
Scenario('addDepartment', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addDepartment();
});
