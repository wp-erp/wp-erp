const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@HRM addEmployee', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addEmployee();
});
