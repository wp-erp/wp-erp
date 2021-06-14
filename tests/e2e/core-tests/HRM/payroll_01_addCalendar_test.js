const helpers = require('../../pages/helpers');
Feature('Payroll');

Scenario('@Payroll addPayrollCalendar',({ I, loginAs}) => {
    loginAs('admin');
    helpers.proActivate();
    helpers.payroll();
    I.click('Add New Pay Calendar');
    I.fillField("//input[@type='text']", "Updated automation");
    I.click('//*[@id="dashboard-widgets-wrap"]/div/div[1]/div/div[2]/select');
    I.click('//*[@id="dashboard-widgets-wrap"]/div/div[1]/div/div[2]/select/option[4]');
    I.click('Add Employee');
    I.checkOption('Engineering');
    I.click('Add employee to list');
    I.wait('2');
    I.click('Create Pay Calendar');
    I.wait(3);
    I.click('Confirm');

});
