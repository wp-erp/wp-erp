Feature('Designation');

Scenario('addDepartment', ({ I }) => {
I.loginAsAdmin()
I.click('WP ERP')
I.click('HR')
I.click('//*[@id="wpbody-content"]/div[2]/ul/li[4]/a')
I.click('//*[@id="erp-new-designation"]')
I.fillField('Designation Title','Graphic Designer')
I.fillField('Description','Software development')
I.click('Create Designation')
I.see('Designations')


});
