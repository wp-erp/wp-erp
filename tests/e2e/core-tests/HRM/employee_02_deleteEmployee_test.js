const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@Employee deleteEmployee', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.deleteEmployee();
});
