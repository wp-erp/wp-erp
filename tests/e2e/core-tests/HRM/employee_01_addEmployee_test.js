
//var faker = require('faker');
const helper = require('../../pages/helper');


const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@Employee addEmployee', ({
    I
}) => {
    I.loginAsAdmin();
    helper.addNewEmployee();
    helpers.addEmployee();
});
