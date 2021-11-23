const helpers = require('../../pages/helpers');
Feature('Location');

Scenario('@Accounting Create new location',({ I, loginAs }) => {
    loginAs('admin');
    	I.click('WP ERP');
    	I.click('Company');
    	I.click('#erp-company-new-location');
    	I.fillField('#location_name', 'Mirpur');
    	I.fillField('#address_1', 'Kazi Para');
    	I.fillField('#address_2', 'Dhanmondi');
    	I.fillField('#city', 'Dhaka');
    	I.click("//*[@id='erp-new-location']/form/div/div/ul/li[5]/span");
    	I.fillField('.select2-search__field', 'Bangladesh');
    	I.click('//input[@type="search"]');
    	I.fillField('#zip', '1216');
		I.click('Create');
});
