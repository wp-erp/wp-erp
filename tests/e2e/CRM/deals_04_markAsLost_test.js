Feature('Deals');

Scenario('@Deals Mark as Lost', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
/*   Mark as Lost Deals   */
I.click('CRM')
I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[4]/a')
I.click('All Deals')
I.click('wedevs deal')
I.click('Lost')
I.click('//*[@id="lost-reason-modal-body"]/div[1]/div/div[2]/span')
I.pressKey("ArrowDown")
I.pressKey("ArrowDown")
I.pressKey("Enter")
I.wait(2)
I.click('//*[@id="lost-reason-modal-body"]/div[2]/input')
I.type('test purpose')
I.click('Mark as Lost')
I.wait(2)

});
