const helpers = require('../../pages/helpers');
Feature('Department');
Scenario('addDepartment', ({
    I
}) => {
    I.loginAsAdmin();
    helpers.addDepartment();
});
