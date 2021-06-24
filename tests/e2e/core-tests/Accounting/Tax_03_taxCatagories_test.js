Feature('Tax');

Scenario('@Tax addTaxCatagories',({ I }) => {
    I.loginAsAdmin();
        I.click('WP ERP');
        I.click('Accounting');
        I.amOnPage('wp-admin/admin.php?page=erp-accounting#/settings/taxes/categories');
        I.wait(5);
        I.click('Add Tax Category');
        I.fillField('//input[@type="text"]', 'Standard');
        I.fillField('//textarea', 'Listing basic tax category');
        I.click('Save');
        I.wait(4);
       
});
