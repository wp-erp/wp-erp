const helpers = require('../../pages/helpers');
Feature('Employee');
Scenario('@HRM addEmployee', ({ I, loginAs}) => {
    loginAs('admin');
    // for(x=0; x<=5; x++){
        helpers.hrmDashboard();
        helpers.peoplePage();
        helpers.addEmployee();
    // }
    
});
