const helper = require('../../pages/helper');
Feature('Company');

Scenario('Create a company',({ I, loginAs }) => {
    loginAs('admin');
    helper.addNewCompany();
});
