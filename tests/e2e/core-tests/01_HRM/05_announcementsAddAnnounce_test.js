const helpers = require('../../pages/helpers');
Feature('Announcement');
Scenario('@HRM Publishing an Announcement', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.hrmDashboard();
    helpers.peoplePage();
    helpers.addAnnouncement();
});
