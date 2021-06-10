const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@Employee deleteEmployee', ({
    I
}) => {
    I.loginAsAdmin();
    helpers.deleteEmployee();
});
