const helpers = require('../../pages/helpers');
Feature('Purchase');

Scenario('@Accounting Assign products',({ I, loginAs }) => {
    loginAs('admin');
        helpers.accDashboard();
        helpers.previewProducts();
        I.click('Products & Services');
        I.wait(3);
        I.click('//*[@id="erp-accounting"]//tr[1]/td[8]/div/div/a');
        I.click('//*[@id="erp-accounting"]//tr[1]/td[8]//ul/li[1]/a');
        I.uncheckOption('//*[@id="wperp-product-modal"]//form/div[4]/div[2]/div/div[2]/input');
        I.click('//*[@id="wperp-product-modal"]//div[2]/div/div[4]/div/div/div[1]');
        I.click('//*[@id="wperp-product-modal"]//div[4]/div/div/div[3]/ul/li[1]/span');
        I.click('Update');

});
