const helpers = require('../../pages/helpers');
Feature('Contact Group');
Scenario('Create Contact Group',({ I, loginAs }) => {
    loginAs('admin');
    helpers.crmDashboard();
    helpers.contactPage();
    helpers.addNewContactGroup();
});
