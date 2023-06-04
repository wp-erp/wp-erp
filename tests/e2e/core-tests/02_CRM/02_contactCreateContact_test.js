const helpers = require('../../pages/helpers');
Feature('Contact');
Scenario('@CRM Create Contact',({ I, loginAs }) => {
   loginAs('admin');
   // for(x=0; x<=15; x++){
      helpers.crmDashboard();
      helpers.contactPage();
      helpers.addNewContact(); 
   // }     
});
