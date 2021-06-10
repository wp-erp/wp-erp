const { helpers } = require('faker');
const helpers = require('../../pages/helper');
Feature('Company');
Scenario('Create a company',({ I, loginAs }) => {
    loginAs('admin');
    helpers.addNewCompany();
});
