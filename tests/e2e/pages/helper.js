const { I } = inject();
var faker = require('faker');
var moment = require('moment');

module.exports = {

  addNewEmployee() {
    I.amOnPage('/wp-admin/admin.php?page=erp-hr&section=people');
    I.click('#erp-employee-new');
    I.fillField('First Name', faker.name.firstName());
    I.fillField('Middle Name',faker.name.middleName());
    I.fillField('Last Name',faker.name.lastName());
    I.fillField('Employee ID',faker.random.number());
    I.fillField('Email',faker.internet.email());
    I.fillField('Employee End Date', moment(faker.date.future()).format("YYYY-MM-DD"));
    I.fillField('Date of Hire', moment(faker.date.past()).format("YYYY-MM-DD"));
    I.click('//*[@id="select2-worktype-container"]');
    I.click('//span[2]/ul/li[2]');
    I.click('//*[@id="select2-workstatus-container"]');
    I.click('//span[2]/ul/li[2]');
    I.click('Create Employee');
    I.wait(2); 
  },

  deleteEmployee() {
    I.amOnPage('/wp-admin/admin.php?page=erp-hr&section=people');
    I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]');
    I.doubleClick('Delete');
    I.wait(2);
    I.acceptPopup();
  },

  addNewCompany() {
    I.amOnPage('/wp-admin/admin.php?page=erp-crm&section=contact');
    I.click('Companies');
    I.click('#erp-company-new');
    I.fillField('#company', faker.company.companyName());
    I.fillField('#erp-crm-new-contact-email', faker.internet.email());
    I.fillField('contact[main][phone]', faker.phone.phoneNumber());
    I.click('#select2-contactmetalife_stage-container');
    I.click('//span[2]/ul/li[2]');
    I.click('//span[@id="select2-erp-crm-contact-owner-id-container"]');
    I.click('//span[2]/ul/li[2]');
    I.click('//button[contains(text(),"Add New")]');
  },


}
