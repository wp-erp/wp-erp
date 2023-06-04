const { helper } = require('codeceptjs');
const helpers = require('../../pages/helpers');

Feature('Leave');

Scenario('@HRM @Leave requestForLeaveAdmin',({ I, loginAs}) => {
    loginAs('admin');
        helpers.hrmDashboard();
        helpers.leave();
        helpers.sendLeaveRequestByAdmin();     

});
