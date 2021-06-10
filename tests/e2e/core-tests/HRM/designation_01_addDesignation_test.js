const helpers = require('../../pages/helpers');
Feature('Designation');
Scenario('addDepartment', ({
    I
}) => {
    I.loginAsAdmin();
    helpers.addDesignation();
});
