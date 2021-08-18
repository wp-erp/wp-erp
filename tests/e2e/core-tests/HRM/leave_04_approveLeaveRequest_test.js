const helpers = require('../../pages/helpers');
Feature('Leave');

Scenario('@Leave approveLeaveRequest',({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        I.click('Requests');
        I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]');
        I.click('Approve');
        I.click('#erp-hr-leave-approve-reason');
        I.type('I am so sick');
        I.click('Approve Request');
        I.see('Approved');
});
