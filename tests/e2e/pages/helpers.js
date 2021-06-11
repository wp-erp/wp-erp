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
    addEmployee() {
        I.amOnPage('/wp-admin/admin.php?page=erp-hr&section=people');
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
        I.amOnPage('wp-admin/admin.php?page=erp-hr&section=people');
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
    
//CRM module
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
