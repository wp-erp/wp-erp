const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@Employee addEmployee', ({
    I
}) => {
    I.loginAsAdmin();
    helpers.addEmployee();
});
