Feature('Leave');

Scenario('@Leave addLeaveEntitlement', ({ I }) => {
I.loginAsAdmin()
I.click('WP ERP')
I.click('HR')
I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[6]')
I.click('Leave Entitlements')
I.click('#erp-new-leave-request')
I.click('#leave_policy')
I.click('//*[@id="leave_policy"]/option[2]')
I.scrollPageToBottom()
I.checkOption('#assignment_to')
I.wait(2)
I.scrollPageToTop()
I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[6]')
I.click('Leave Entitlements')
I.see('Leave Entitlements')



});
