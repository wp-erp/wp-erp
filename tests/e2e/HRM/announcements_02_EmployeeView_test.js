Feature('Announcement');

Scenario('@Announcement Viewing Announcements as a Employee', ({ I }) => {
I.loginAsEmployee()
I.click('WP ERP')
I.see('Announcement')



})
