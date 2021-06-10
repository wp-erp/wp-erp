//var faker = require('faker');
const helper = require('../../pages/helper');

Feature('Employee');
Scenario('@Employee addEmployee',({ I }) => {  
    I.loginAsAdmin();
    helper.addNewEmployee();
});
