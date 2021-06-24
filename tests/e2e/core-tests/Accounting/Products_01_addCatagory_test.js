Feature('Products');

Scenario('@Products addCatagory',({ I }) => {
    I.loginAsAdmin();
        I.click('WP ERP');
        I.click('Accounting');
        // I.wait(5);
        I.moveCursorTo('//*[@id="erp-accounting"]/div[1]/ul/li[4]/a');
        I.click('Product Categories');
        I.fillField('.wperp-form-field', 'Asset');
        I.click('Save');
});
