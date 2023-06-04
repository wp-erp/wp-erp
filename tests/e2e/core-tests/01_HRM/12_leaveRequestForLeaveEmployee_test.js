const helpers = require('../../pages/helpers');

Feature('Leave');

Scenario('@HRM @Leave requestForLeaveEmployee',({ I, loginAs}) => {
    loginAs('employee');
    helpers.sendLeaveRequestByEmployee();  
});
