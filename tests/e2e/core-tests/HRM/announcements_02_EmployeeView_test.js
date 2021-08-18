Feature('Announcement');
Scenario('@Announcement Viewing Announcements as a Employee', ({ I, loginAs}) => {
    loginAs('employee');
    I.click('WP ERP');
    I.see('Announcement');
})
