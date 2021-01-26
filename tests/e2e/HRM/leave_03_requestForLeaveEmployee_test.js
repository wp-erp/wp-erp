Feature('Leave');
Scenario('@Leave requestForLeaveEmployee', ({ I }) => {
I.loginAsEmployee()
I.click('WP ERP')
I.click('HR')
I.click('Take a Leave')
I.click('#erp-hr-leave-req-leave-policy')
I.click('//*[@id="erp-hr-leave-req-leave-policy"]/option[2]')
I.fillField('From','2020-12-24')
I.fillField('To','2020-12-24')
I.pressKey('Enter')
I.fillField('Reason','demo')
I.click('Send Leave Request')
I.acceptPopup()


});
