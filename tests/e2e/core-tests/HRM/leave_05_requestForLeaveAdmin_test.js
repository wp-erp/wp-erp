const helpers = require('../../pages/helpers');
Feature('Leave');

Scenario('@Leave requestForLeaveAdmin',({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        I.click('Requests');
        I.click('New Request');
        I.click('- Select Employee -');
        I.click('/html/body/span/span/span[2]/ul/li[2]');
        I.click('Leave Type');
        I.click('//*[@id="erp-hr-leave-req-leave-policy"]/option[2]');
        I.fillField('From','2020-10-23');
        I.fillField('To', '2020-10-23');
        I.click('#leave_reason');
        I.type('Sending Leave request for an employee for HR');
        I.click('#submit');
        I.wait(3);

});
