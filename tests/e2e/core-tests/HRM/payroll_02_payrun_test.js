const helpers = require('../../pages/helpers');
Feature('Payroll');

Scenario('@Payroll payrun', ({ I, loginAs}) => {
    loginAs('admin');
    helpers.payroll();
    I.click('//*[@id="dashboard-widgets-wrap"]/div/div[2]/div[2]/span[2]');
    I.wait('2');
    I.fillField("//div[@id='pay-run-wrapper-employees']/div/div/input", "2021-06-01")
    I.fillField("//div[@id='pay-run-wrapper-employees']/div/div/input[2]", "2021-06-15")
    I.fillField("//div[@id='pay-run-wrapper-employees']/div/div[2]/input", "2021-06-28")
    //I.click('div:nth-of-type(1) > input:nth-of-type(1)'); 
    //I.click('//*[@id="ui-datepicker-div"]/table/tbody/tr[1]/td[4]/a');
    //I.click('div:nth-of-type(1) > input:nth-of-type(2)'); 
    //I.click('//*[@id="ui-datepicker-div"]/table/tbody/tr[3]/td[4]/a');
    //I.click('div:nth-of-type(2) > .hasDatepicker'); 
    //I.click('//*[@id="ui-datepicker-div"]/table/tbody/tr[5]/td[5]/a');
    I.click('Generate Employee List');
    I.scrollPageToBottom();
    I.wait(2);
    I.click('//*[@id="pay-run-wrapper-employees"]/div/div[3]/div[3]/button')
    I.wait(3);
    I.click('//*[@id="pay-run-wrapper-variable-input-tab"]/div[2]/div[1]/div/div[1]/select');
    I.click('//*[@id="pay-run-wrapper-variable-input-tab"]/div[2]/div[1]/div/div[1]/select/option[1]');
    I.click('//*[@id="pay-run-wrapper-variable-input-tab"]/div[2]/div[1]/div/div[1]/select');
    I.click('//*[@id="pay-run-wrapper-variable-input-tab"]/div[2]/div[1]/div/div[1]/input[1]');
    I.type('2000');
    I.click('//*[@id="pay-run-wrapper-variable-input-tab"]/div[2]/div[2]/div/a[1]');
    I.scrollPageToBottom();
    I.click('//*[@id="pay-run-wrapper-payslips-tab"]/div[2]/a[1]');
    I.click('Approve');
    I.wait('3');
    I.click('Confirm');
    I.see('Pay Run List');

});