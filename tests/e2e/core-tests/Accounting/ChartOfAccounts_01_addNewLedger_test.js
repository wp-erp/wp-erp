Feature('Chart of accounts');

Scenario('Add new ledger',({ I }) => {
	I.loginAsAdmin();
		I.click('WP ERP');
		I.click('Accounting');
		I.moveCursorTo('//*[@id="erp-accounting"]/div[1]/ul/li[5]/a');
    	I.click('Chart of Accounts');
    	I.wait(2);
    	I.click({css : '.wperp-btn'});
    	I.click('.vue-treeselect__input');
    	I.wait(5);
    	I.click('//div[7]/div/div/label');
    	I.fillField('//div[2]/input', 'Agrani');
    	I.fillField('//form/div[3]/input', '23543543535');
		I.click('Save');

});
