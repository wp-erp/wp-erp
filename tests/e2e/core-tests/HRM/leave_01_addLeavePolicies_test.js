const { helper } = require('codeceptjs');
const helpers = require('../../pages/helpers');
Feature('Leave');

Scenario('@Leave addLeavePolicy',({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        I.click('Policies');
        I.click('#erp-leave-name-new');
        I.fillField('Leave Type','Sick Leave');
        I.fillField('Description','For All departments');
        I.click('Save');
        I.click('Back To Leave Policies');
        I.click('#erp-leave-policy-new');
        I.click('//*[@id="leave-id"]/option[4]');
        I.fillField('Description','Testing automation');
        I.fillField('Days','20');
        I.checkOption('Entitle New Employees');
        I.checkOption('Apply for existing employees');
        I.click('Save');
        I.see('Leave Policies');
        
});
