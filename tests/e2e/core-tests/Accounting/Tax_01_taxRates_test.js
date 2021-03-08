Feature('Tax');

Scenario('@Tax addTaxRate',({ I }) => {
    I.loginAsAdmin();
        // Add new tax
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes/rate-names');
        I.wait(5);
        I.click('Add Tax Zone');
        I.fillField('//input[@type="text"]', 'Dhaka');
        I.fillField('//div[2]/input', '34543534');
        I.click('Save');

        // Add new tax category
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes/categories');
        I.wait(5);
        I.click('Add Tax Category');
        I.fillField('//input[@type="text"]', 'Standard');
        I.fillField('//textarea', 'Listing basic tax category');
        I.click('Save');

        // Add tax agencies
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes/agencies');
        I.wait(5);
        I.click('Add Tax Agency');
        I.fillField('//input[@type="text"]', 'Agency');
        I.click('Save');

        //Add new tax rates
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes');
        I.wait(5);
        I.click('Add Tax Rate');
        I.click('div.multiselect__tags');
        I.wait(5);
        I.click('//div[3]/ul/li/span/span');
        I.fillField('//td/input', 'Food');
        I.click('//td[@class="col--agency with-multiselect"]//span[@class="multiselect__placeholder"][contains(text(),"Please search")]');
        I.wait(5);
        I.click('//td[2]/div/div[3]/ul/li/span/span');
        I.click('//td[@class="col--tax-category with-multiselect"]//span[@class="multiselect__placeholder"][contains(text(),"Please search")]');
        I.click('//td[3]/div/div[3]/ul/li/span');
        I.fillField('//td[4]/input', '10');
        I.click('Save');

        //Tax Payments
        // I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/taxes/tax-records');
        // I.wait(10);
        // I.click('New Tax Payment');
        // I.click('//div[@class="wperp-col-sm-4 with-multiselect"]//span[@class="multiselect__single"]');
        // I.wait(5);
        // I.click('//div[3]/ul/li/span');
        // I.click('//span[@class="multiselect__placeholder"]');
        // I.wait(5);
        // I.click('//div[2]/div/div/div[3]/ul/li/span');
        // I.click('//div[4]//div[1]//div[1]//div[2]//span[1]');
        // I.wait(5);
        // I.click('//div[4]/div/div/div[3]/ul/li/span/span');
        // I.fillField('//input[@type="number"]', '120');
        // I.click('Save');
        // I.waitForElement('.app-customers', 30);
});
