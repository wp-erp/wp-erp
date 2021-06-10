Feature('Leave');

Scenario('@Leave addLeavePolicy',({ I }) => {
    I.loginAsAdmin();
        I.click('WP ERP');
        I.click('HR');
        I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[3]');
        I.click('Policies');
        I.click('#erp-leave-name-new');
        I.fillField('Leave Type','Sick Leave');
        I.fillField('Description','For All departments');
        I.click('Save');
        I.click('Back To Leave Policies');
        I.click('#erp-leave-policy-new');
        I.click('//*[@id="leave-id"]/option[4]');
        I.fillField('Description','Testing automation');
        I.fillField('Days','20');
        I.checkOption('Entitle New Employees');
        I.checkOption('Apply for existing employees');
        I.click('Save');
        I.see('Leave Policies');


});
