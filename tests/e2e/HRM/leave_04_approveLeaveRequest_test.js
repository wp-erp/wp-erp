Feature('Leave');
Scenario('@Leave approveLeaveRequest', ({ I }) => {
I.loginAsAdmin()
I.click('WP ERP')
I.click('HR')
I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[6]')
I.click('Requests')
I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]')
I.click('Approve')
I.click('#erp-hr-leave-approve-reason')
I.type('I am so sick')
I.click('Approve Request')
I.see('Approved')


});
