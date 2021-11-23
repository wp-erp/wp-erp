const { helper } = require('codeceptjs');
const helpers = require('../../pages/helpers');
Feature('Leave');

Scenario('@HRM addLeavePolicy',async ({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        I.click('Policies');
        I.click('#erp-leave-name-new');
        helpers.leavePolicy();
        
        // const yearNotFound = await I.see('//*[@id="f-year"]/option[1]');
        // if (yearNotFound >= 1) {
        //     I.click('//*[@id="wpbody-content"]//div[1]/span/a');
        //     I.wait(3);
        //     I.click('.wperp-btn .wperp-btn-default');
        //     I.click('//*[@id="erp-settings-box-erp-hr-financial"]/form/div[3]/div[1]/input');
        //     I.fillField('Start Date','2022-01-01');
        //     I.fillField('End Date','2022-12-01');
        // }
        // else{
        //     helpers.leavePolicy();
        // } 
        
});
