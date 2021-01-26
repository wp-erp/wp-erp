Feature('CRM Module Activate & Deactive');

Scenario('@module Activating & Deactivating CRM Modules with Extention', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
I.click('Modules')
/*   Activate    */
I.click("//div[@id='wpbody-content']/div[2]/div/div[2]/div/div[2]/div/div[2]/label/span")
I.wait(2)
I.click('//*[@id="crm"]')
I.wait(3)
I.click("//input[@value='awesome_support']")
I.checkOption('Select All')
I.click('Activate')
I.click('#close_table_nav_btn')
/*   Deactivate  */  
I.click("//div[@id='wpbody-content']/div[2]/div/div[2]/div/div[2]/div/div[2]/label/span")
I.wait(4)
I.click('//*[@id="crm"]')
I.click("//input[@value='awesome_support']")
I.checkOption('Select All')
I.click('Deactivate')
I.click('#close_table_nav_btn')

});

