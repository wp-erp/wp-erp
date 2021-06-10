const helpers = require('../../pages/helpers');
Feature('Announcement');
Scenario('@Announcement Publishing an Announcement', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.addAnnouncement();
});
