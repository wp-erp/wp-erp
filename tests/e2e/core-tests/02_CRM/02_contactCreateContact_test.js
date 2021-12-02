const helpers = require('../../pages/helpers');
Feature('Contact');
Scenario('@CRM Create Contact',({ I, loginAs }) => {
   loginAs('admin');
   helpers.crmDashboard();
   helpers.contactPage();
   helpers.addNewContact();      
});
