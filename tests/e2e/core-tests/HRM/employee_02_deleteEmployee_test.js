const helper = require('../../pages/helper');

const helpers = require('../../pages/helpers');

Feature('Employee');
Scenario('@Employee deleteEmployee', ({
    I
}) => {
    I.loginAsAdmin();
    helper.deleteEmployee();
    helpers.deleteEmployee();
});
