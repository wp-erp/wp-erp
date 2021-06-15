const helpers = require('../../pages/helpers');
Feature('Contact');
Scenario('Create Contact',({ I, loginAs }) => {
   loginAs('admin');
   helpers.crmDashboard();
   helpers.contactPage();
   helpers.addNewContact();      
});
