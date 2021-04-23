Feature('Designation');

Scenario('addDepartment',({ I }) => {
    I.loginAsAdmin();
        I.click('WP ERP');
        I.click('HR');
        I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]');
        I.click('Designations');
        I.click('//*[@id="erp-new-designation"]');
        I.fillField('Designation Title','Product Designer');
        I.fillField('Description','Software development');
        I.click('Create Designation');
        I.see('Designations');
        


});
