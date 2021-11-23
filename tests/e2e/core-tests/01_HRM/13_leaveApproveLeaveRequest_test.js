const helpers = require('../../pages/helpers');
Feature('Leave');

Scenario('@HRM approveLeaveRequest', async ({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        I.click('Requests');
        I.click('//*[@id="wpbody-content"]//form/ul/li[3]/a');
        const pending = await I.grabNumberOfVisibleElements('#the-list .no-items');
        if (pending >= 1) {
            I.click('//*[@id="wpbody-content"]//form/ul/li[2]/a');
            I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]/a');
            I.click('Reject');
            I.click('//*[@id="erp-hr-leave-reject-reason"]');
            I.type('Test purpose');
            I.click('Reject Request');
            // I.click('//*[@id="wpbody-content"]//form/ul/li[4]/a');
            // I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]/a');
            // I.click('Approve');
            // I.click('#erp-hr-leave-approve-reason');
            // I.type('I am so sick');
            // I.click('Approve Request');
            // I.see('Approved');
        }
        else{
            I.moveCursorTo('//*[@id="the-list"]/tr/td[1]/a');
            I.click('Approve');
            I.click('#erp-hr-leave-approve-reason');
            I.type('I am so sick');
            I.click('Approve Request');
            I.see('Approved');
        }
            
});
