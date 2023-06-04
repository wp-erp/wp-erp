Feature('Announcement');
Scenario('@HRM Viewing Announcements as a Employee', ({ I, loginAs}) => {
    loginAs('employee');
    I.amOnPage('wp-admin/admin.php?page=erp-hr');
    I.see('Announcement');
})