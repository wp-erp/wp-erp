const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@Employee addEmployee', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addEmployee();
});
