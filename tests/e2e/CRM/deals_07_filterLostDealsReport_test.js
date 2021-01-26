Feature('Deals');

Scenario('@Deals Filter Lost Deals and View Report', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
/*   Filter   */
I.click('CRM')
I.click('//*[@id="wpbody-content"]/div[2]/ul/li[4]/a')
I.click('Lost Deals')
I.wait(3)
I.scrollPageToBottom()
I.wait(2)
I.scrollPageToTop()
I.wait(2)
I.click('Close')
I.wait(2)
I.click('This month')
I.click('This week')
I.click('Open Deals')
I.click("(//a[contains(@href, '#')])[39]")
I.see('Lost Deals')

});
