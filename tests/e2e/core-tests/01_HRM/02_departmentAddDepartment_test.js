const helpers = require('../../pages/helpers');
Feature('Department');
Scenario('@HRM addDepartment', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addDepartment();
});
