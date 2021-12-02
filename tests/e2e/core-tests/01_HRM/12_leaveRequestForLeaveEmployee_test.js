const helpers = require('../../pages/helpers');

Feature('Leave');

Scenario('@HRM requestForLeaveEmployee',({ I, loginAs}) => {
    loginAs('employee');
    helpers.sendLeaveRequestByEmployee();  
});
