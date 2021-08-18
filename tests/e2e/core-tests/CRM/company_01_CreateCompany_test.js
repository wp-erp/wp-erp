const helpers = require('../../pages/helpers');
Feature('Company');
Scenario('Create a company',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    helpers.contactPage();
    helpers.addNewCompany();
});
