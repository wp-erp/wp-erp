var Factory = require('rosie').Factory;
var faker = require('faker');
var moment = require('moment');
const {
 helper
} = require("codeceptjs");
const {
    helpers
} = require("faker");
const {
    I
} = inject();

module.exports = {
//HRM Module
    hrmDashboard(){
        I.amOnPage('wp-admin/admin.php?page=erp-hr');
    },

    peoplePage(){
        I.amOnPage('/wp-admin/admin.php?page=erp-hr&section=people');
    },

    proActivate(){
        I.click('.menu-icon-plugins > .wp-menu-name');
        I.click('#activate-wp-erp-pro');
    },

    addEmployee() {
        I.click('//*[@id="erp-employee-new"]');
        I.fillField('First Name', faker.name.firstName());
        I.fillField('Middle Name', faker.name.middleName());
        I.fillField('Last Name', faker.name.lastName());
        I.fillField('Employee ID', faker.random.number());
        I.fillField('Email', faker.internet.email());
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
        I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]');
        I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]');
        I.doubleClick('Delete');
        I.acceptPopup();
    },

    addDepartment() {
        I.amOnPage('wp-admin/admin.php?page=erp-hr&section=people&sub-section=department');
        I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]');
        I.click('Departments');
        I.click('//*[@id="erp-new-dept"]');
        I.fillField('Department Title', 'Business');
        I.fillField('Description', 'Content development');
        I.click('//*[@id="dept-lead"]');
        I.click('//*[@id="dept-lead"]/option[2]');
        I.click('//*[@id="dept-parent"]');
        I.click('Create Department');
        I.see('Departments');
    },

    addDesignation() {
        I.amOnPage('wp-admin/admin.php?page=erp-hr&section=people&sub-section=designation');
        I.click('//*[@id="wpbody-content"]/div[2]/ul/li[2]');
        I.click('Designations');
        I.click('//*[@id="erp-new-designation"]');
        I.fillField('Designation Title', 'Product Designer');
        I.fillField('Description', 'Software development');
        I.click('Create Designation');
        I.see('Designations');
    },
    addAnnouncement() {
        I.amOnPage('wp-admin/edit.php?post_type=erp_hr_announcement');
        I.amOnPage('wp-admin/post-new.php?post_type=erp_hr_announcement')
        I.click('//*[@id="title"]');
        I.fillField('Add title', 'Testing by Rinky');
        I.click('/html/body');
        I.type('Rinky_automation');
        I.click('//*[@id="hr_announcement_assign_type"]');
        I.click('//*[@id="hr_announcement_assign_type"]/option[2]');
        I.wait(3);
        I.forceClick('Publish');
    },
    leave() {
        I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[3]');       
    },
    payroll() {
        I.amOnPage('wp-admin/admin.php?page=erp-hr');
        I.moveCursorTo('//*[@id="wpbody-content"]/div[3]/ul/li[3]/a');
        I.click('Pay Calendar');
    },
    
//CRM module
    crmDashboard(){
        I.amOnPage('wp-admin/admin.php?page=erp-crm');
    },

    contactPage(){
        I.amOnPage('/wp-admin/admin.php?page=erp-crm&section=contact');
    },

    dealsPage(){
        I.moveCursorTo('//*[@id="wpbody-content"]/div[2]/ul/li[3]/a');
        I.click('All Deals');
    },
    addNewContact(){       
        I.click('#erp-customer-new');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#erp-crm-new-contact-email', faker.internet.email());
        I.fillField('contact[main][phone]', faker.phone.phoneNumber());
        I.click('#select2-contactmetalife_stage-container');
        I.click('//span[2]/ul/li[2]');
        I.click('//span[@id="select2-erp-crm-contact-owner-id-container"]');
        I.click('//span[2]/ul/li[2]');
        I.click('//button[contains(text(),"Add New")]');
        I.waitForElement('#wp-erp', 30);
    },

    addNewCompany() {
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

    addNewContactGroup(){
        I.click('Contact Groups');
        I.click('#erp-new-contact-group');
        I.fillField('#erp-crm-contact-group-name', 'Basic');
        I.fillField('#erp-crm-contact-group-description', 'Hers is the contact group description');
        I.click('//button[contains(text(),"Add New")]');
    },

//Accounting module 
    accDashboard(){
        I.amOnPage('/wp-admin/admin.php?page=erp-accounting#/');
        I.wait(5);
    },

    previewTransactions(){
        I.moveCursorTo('//div[2]/div/div[2]/div/div/ul/li[3]/a');
    },

    previewSettings(){
        I.moveCursorTo('//*[@id="erp-accounting"]/div[1]/ul/li[5]/a');
    },

    Expense(){
        I.click('Expenses');
        I.wait(5);
        I.click('//*[@id="erp-accounting"]/div[2]/div[1]/div/div/div/div');
    },

    Journal(){
        I.click('Journals');
        I.wait(5);
        I.click('.erp-journal-new');
        I.click('//td[2]/div/div/div[2]');
        I.wait(5);
        I.click('//li[6]/span/span');
        I.fillField('//td[4]/input', '20000');
        I.click('//tr[2]/td[2]/div/div/div[2]');
        I.click('//tr[2]/td[2]/div/div/div[3]/ul/li[2]/span/span');
        I.fillField('//tr[2]/td[5]/input', '20000');
        I.click('Save');
    },

    previewUsers() {
        I.moveCursorTo('//div[2]/div/div[2]/div/div/ul/li[2]/a');
      },

    addCustomer(){
        I.click('Customers');
        I.wait(4);
        I.click('Add New Customer');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#email', faker.internet.email());
        I.fillField('#phone', faker.phone.phoneNumber());
        I.fillField('#company', faker.company.companyName());
        I.click('//*[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');
        I.waitForElement('.app-customers');
      },

      previewProducts() {
        I.moveCursorTo('//*[@id="erp-accounting"]/div[1]/ul/li[4]/a');
         
      },

      addProducts(){
        I.click('Products & Services');
        I.wait(3);
        I.click('#erp-product-new');
        I.wait(3);
        I.fillField('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[1]/div[2]/input', faker.commerce.productName());
        I.click('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[2]/div[2]/div[1]/div[2]/div/div/div[2]/span');
        I.click('//*[@id="wperp-product-modal"]/div/div/div/div/div[2]/form/div[2]/div[2]/div[1]/div[2]/div/div/div[3]/ul/li[1]/span/span');
        I.fillField('#cost-price', '350');
        I.fillField('#sale-price', '400');
        I.pressKey('Tab');
        I.checkOption('form input[value=self]');
        I.click('Save');  
        I.waitForElement('.wperp-products');
      },



}
