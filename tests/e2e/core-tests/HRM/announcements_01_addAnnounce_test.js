const helpers = require('../../pages/helpers');
Feature('Announcement');
Scenario('@Announcement Publishing an Announcement', ({
    I
}) => {
    I.loginAsAdmin();
    helpers.addAnnouncement();
});
