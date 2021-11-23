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

});
