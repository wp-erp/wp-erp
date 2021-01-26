Feature('Accounting Module Activate & Deactive');

Scenario('@module Activating & Deactivating Accounting Modules with Extention', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
I.click('Modules')
    /*   Activate    */
I.click("//div[@id='wpbody-content']/div[2]/div/div[2]/div/div[3]/div/div[2]/label/span")
I.click('Accounting')
I.wait(2)
I.click("//input[@value='inventory']")
I.checkOption('Select All')
I.click('Activate')
I.click('#close_table_nav_btn')
/*   Deactivate   */ 
I.click("//div[@id='wpbody-content']/div[2]/div/div[2]/div/div[3]/div/div[2]/label/span")
I.wait(2)
I.click('Accounting')
I.click("//input[@value='inventory']")
I.checkOption('Select All')
I.click('Deactivate')
I.click('#close_table_nav_btn')

});


