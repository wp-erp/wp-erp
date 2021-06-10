const helpers = require('../../pages/helpers');
Feature('Department');
Scenario('addDepartment', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.addDepartment();
});
