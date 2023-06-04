const helpers = require('../../pages/helpers');
Feature('Department');
Scenario('@ALLHRM addDepartment', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addEmployee();
    helpers.addDepartment();
    helpers.addDesignation();
    helpers.addAnnouncement();
    helpers.deleteEmployee();
});
