const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@Employee deleteEmployee', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.deleteEmployee();
});
