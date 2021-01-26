Feature('Employee');

Scenario('@Employee addEmployee', ({ I }) => {
I.loginAsAdmin()
I.click('WP ERP')
I.click('HR')
I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]/a')
I.click('//*[@id="erp-employee-new"]')
I.fillField('First Name','Lutfun')
I.fillField('Middle Name','Naher')
I.fillField('Last Name','Rinky')
I.fillField('Employee ID','1551')
I.fillField('Email','lutfun9999@yahoo.com')
I.fillField('Employee End Date','2019-06-01')
I.fillField('Date of Hire','2020-12-30')
I.click('//*[@id="select2-worktype-container"]')
I.click('//span[2]/ul/li[2]')
I.click('//*[@id="select2-workstatus-container"]')
I.click('//span[2]/ul/li[2]')
I.click('Create Employee')
I.wait(2)



});
