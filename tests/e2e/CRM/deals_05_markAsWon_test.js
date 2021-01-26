Feature('Deals');

Scenario('@Deals Mark as Won', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
/*   Mark as Won Deals   */
I.click('CRM')
I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[4]/a')
I.click('All Deals')
I.click('wedevs deal')
I.click('Won')
I.see('wedevs deal')
I.wait(2)

});
