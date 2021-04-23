Feature('Tax');

Scenario('@Tax addTaxAgencies',({ I }) => {
    I.loginAsAdmin();
        I.click('WP ERP');
        I.click('Accounting');
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/settings/taxes/agencies');
        I.wait(5);
        I.click('Add Tax Agency');
        I.fillField('//input[@type="text"]', 'Agency');
        I.click('Save');

});
