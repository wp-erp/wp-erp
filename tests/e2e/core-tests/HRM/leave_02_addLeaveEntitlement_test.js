const helpers = require('../../pages/helpers');
Feature('Leave');

Scenario('@Leave addLeaveEntitlement',({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        I.click('Leave Entitlements');
        I.click('#erp-new-leave-request');
        I.scrollPageToBottom();
        I.click('//*[@id="select2-leave_policy-container"]');
        I.type('Sick');
        I.pressKey('Enter') 
        I.checkOption('#assignment_to');
        I.wait(2);
        I.scrollPageToTop();
        I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[6]');
        I.click('Back to Entitlement list');
        I.see('Leave Entitlements');
});
