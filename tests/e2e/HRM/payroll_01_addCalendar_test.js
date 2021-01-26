Feature('Payroll');
Scenario('@Payroll addPayrollCalendar', ({ I }) => {
I.loginAsAdmin()
I.click('WP ERP')
I.click('HR')
I.moveCursorTo('//*[@id="wpbody-content"]/div[3]/ul/li[4]/a')
I.click('Pay Calendar')
I.click('Add New Pay Calendar')
I.fillField("//input[@type='text']", "automation")
I.click('//*[@id="dashboard-widgets-wrap"]/div/div[1]/div/div[2]/select')
I.click('//*[@id="dashboard-widgets-wrap"]/div/div[1]/div/div[2]/select/option[2]')
I.click('Add Employee')
I.checkOption('HR')
I.click('Add employee to list')
I.wait('2')
I.click('Create Pay Calendar')
I.wait(2)
I.click('Confirm')  

});
