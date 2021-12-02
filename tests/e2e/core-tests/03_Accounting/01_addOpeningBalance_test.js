const { default: getValue } = require('webdriverio/build/commands/element/getValue');
const helpers = require('../../pages/helpers');
Feature('Opening Balance');

Scenario('Add Opening Balance',async ({ I, loginAs }) => {
    loginAs('admin');
		helpers.accDashboard();
        I.click('//*[@id="wp-admin-bar-wp-erp-acct"]/div[1]');
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/opening-balance');
        const checkBalanceEquity = await I.grabValueFromAll('#erp-accounting > div.wperp-container.accordion-container > form > table > tbody > tr.total-amount-row > td:nth-child(2) > input','0');
        if (checkBalanceEquity) {
            I.click('#erp-accounting > div.wperp-container.accordion-container > form > div:nth-child(4) > table > tbody > tr:nth-child(6) > td:nth-child(2) > input');
            I.wait(2);
            I.type('100000000');
            I.click('#erp-accounting > div.wperp-container.accordion-container > form > div:nth-child(6) > table > tbody > tr:nth-child(5) > td:nth-child(3) > input')
            I.type('100000000');
            I.scrollPageToBottom();
            I.click('#erp-accounting > div.wperp-container.accordion-container > form > button');   
        }
        else
        {
           console.log('Debits & Credits are equal')
        }
    });

