const helpers = require('../../pages/helpers');
Feature('Company');
Scenario('@CRM Create a company',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    helpers.contactPage();
    helpers.addNewCompany();
});
