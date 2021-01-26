Feature('Deals');

Scenario('@Deals Filter new Deals and View Report', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
/*   Filter   */
I.click('CRM')
I.click('//*[@id="wpbody-content"]/div[2]/ul/li[4]/a')
I.click('New Deals')
I.wait(6)
I.scrollPageToBottom()
I.click('Close')
I.wait(4)
I.scrollPageToTop()
I.click('This month')
I.wait(2)
I.click('This week')
I.click('Open Deals')
I.wait(2)
I.click('Open Deals')
I.see('Open Deals')
    
});
