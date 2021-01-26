Feature('Employee');

Scenario('@Employee deleteEmployee', ({ I }) => {
I.loginAsAdmin()
I.click('WP ERP')
I.click('HR')
I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]/a')
I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]')
I.doubleClick('Delete')
I.acceptPopup()


});
