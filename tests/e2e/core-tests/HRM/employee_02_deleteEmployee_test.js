const helper = require('../../pages/helper');

Feature('Employee');

Scenario('@Employee deleteEmployee',({ I }) => {  
    I.loginAsAdmin();
    helper.deleteEmployee();

});
