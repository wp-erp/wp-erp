var faker = require('faker');
Feature('Products');

Scenario('@Products addProducts',({ I }) => {
    I.loginAsAdmin();
        I.previewProducts();
        I.moveCursorTo('//*[@id="erp-accounting"]/div[1]/ul/li[4]/a');
        I.click('Products & Services');
        I.wait(5);
        I.click('Add New Product');
        I.fillField('.wperp-form-field', faker.commerce.productName());
        I.click('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[2]/div[2]/div[1]/div[2]/div/div/div[2]/span');
        I.click('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[2]/div[2]/div[1]/div[2]/div/div/div[3]/ul/li[1]/span/span');
        I.fillField('#cost-price', '350');
        I.fillField('#sale-price', '400');
        I.pressKey('Tab');
        I.checkOption('form input[value=self]');
        I.click('Save');  
        I.waitForElement('.wperp-products');
    
});





