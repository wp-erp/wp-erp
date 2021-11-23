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

    liteActivate(){
      I.click('//*[@id="menu-plugins"]');
      //I.click('.menu-icon-plugins > .wp-menu-name');
      I.click('#activate-erp');
    },

    liteDeactivate(){
      I.click('//*[@id="menu-plugins"]');
      // I.click('.menu-icon-plugins > .wp-menu-name');
      I.click('#deactivate-erp');
      I.click('Submit & Deactivate');
      I.wait(3);
    },

    proActivate(){
      I.click('Plugins');
      I.click('#activate-wp-erp-pro');
    },

    proDeactivate(){
      I.click('#deactivate-wp-erp-pro');
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
        I.moveCursorTo('//*[@id="the-list"]/tr[1]/td[1]');
        I.click('Delete');
        I.acceptPopup();
    },

    addDepartment() {
        I.amOnPage('wp-admin/admin.php?page=erp-hr&section=people&sub-section=department');
        I.click('Departments');
        I.click('//*[@id="erp-new-dept"]');
        I.fillField('Department Title', faker.name.firstName());
        I.fillField('Description', 'Content development');
        I.click('//*[@id="dept-lead"]');
        I.click('//*[@id="dept-lead"]/option[2]');
        I.click('//*[@id="dept-parent"]');
        I.click('Create Department');
        I.see('Departments');
    },

    addDesignation() {
        I.amOnPage('wp-admin/admin.php?page=erp-hr&section=people&sub-section=designation');
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

    sendLeaveRequestByAdmin(){
      I.click('Requests');
      I.click('New Request');
      I.click('- Select Employee -');
      I.click('/html/body/span/span/span[2]/ul/li[2]');
      I.click('Leave Type');
      I.click('//*[@id="erp-hr-leave-req-leave-policy"]/option[3]');
      I.fillField('From', moment(faker.date.recent(30)).format("YYYY-MM-DD"));
      I.fillField('To', moment(faker.date.soon(30)).format("YYYY-MM-DD"));
      I.click('#leave_reason');
      I.type('Sending Leave request for an employee for HR');
      I.click('#submit');
      I.wait(3);
      
    },


    sendLeaveRequestByEmployee(){
      I.amOnPage('/wp-admin/admin.php?page=erp-hr');
      I.click('Take a Leave');
      I.click('#erp-hr-leave-req-leave-policy');
      I.click('//*[@id="erp-hr-leave-req-leave-policy"]/option[3]');
      I.fillField('From', moment(faker.date.recent(60)).format("YYYY-MM-DD"));
      I.fillField('To', moment(faker.date.recent(60)).format("YYYY-MM-DD"));
      I.pressKey('Enter');
      I.fillField('Reason','demo');
      I.click('Send Leave Request');
      I.wait(2);
    },
      leavePolicy(){
        I.fillField('Leave Type','Sick Leave');
        I.fillField('Description','For All departments');
        I.click('Save');
        I.refreshPage();
        I.click('Back To Leave Policies'); 
        I.click('#erp-leave-policy-new');
        I.click('//*[@id="leave-id"]/option[2]');
        I.fillField('Description','Testing automation');
        I.fillField('Days','20');
        I.checkOption('Entitle New Employees');
        I.checkOption('Apply for existing employees');
        I.click('Save');
        I.see('Leave Policies');
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
        I.moveCursorTo('//div/div/div[2]/div/div[1]/ul/li[2]/a');
        // I.moveCursorTo('//*[@id="erp-act-menu-users"]/a');
        //I.moveCursorTo('//div[2]/div/div[2]/div/div/ul/li[2]/a');
      },

    addCustomer(){
        I.click('//*[@id="erp-act-menu-users"]/ul/li[1]/a');
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

      vendor(){
        I.click('Vendors');
        I.wait(5);
        I.click('Add New Vendor');
        I.fillField('#first_name', faker.name.firstName());
        I.fillField('#last_name', faker.name.lastName());
        I.fillField('#email', faker.internet.email());
        I.fillField('#phone', faker.phone.phoneNumber());
        I.fillField('#company', faker.company.companyName());
        I.click('//div[@id="wperp-add-customer-modal"]/div/div/form/div[2]/div/button[2]');
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

      purchase(){
        I.click('Purchases');
        I.wait(5);
        I.click('.wperp-selected-option');
      },

      createPurchase(){
        I.click('//*[@id="erp-accounting"]/div[2]/div[1]/div/div/div/ul/li[1]/a');
        I.click('//*[@id="erp-accounting"]//div/div/div/div[2]/span');
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]/div/div/div[1]/div/div/div[2]');
        I.click('//*[@id="erp-accounting"]/div[2]/form/div[1]//ul/li[1]');
        I.click('//*[@id="erp-accounting"]//div/div/div[2]//div/input');
        I.click('//*/div[2]/div/div/div/div[2]/div/div[3]/div/div/div[3]/div[3]//div[2]/div/div');
        I.click('//*[@id="erp-accounting"]/div[2]/form//div[3]//input');
        I.click('//*/div[3]//div[3]/div/div/div[4]/div[3]//div[2]/div/div');
        I.click('//*/div[2]//table/tbody/tr[1]/th/div/div[2]');
        I.click('//*/tr[1]/th/div//ul/li[1]/span');
        I.wait(2);
        I.click('//*/table//div[1]/button');
        
      },

      payPurchase(){
        I.click('//*[@id="erp-accounting"]//div/div/ul/li[2]/a');
        I.click('//*//div[1]/div/div/div[1]/div/div/div/span');
        I.click('//*/div[1]/div/div/div[1]/div/div/div/ul/li[1]/span');
        I.click('//*//form/div[1]/div/div/div/div/input');
        I.click('//*/div[1]/div/div/div[3]/div/div/input');
        I.click('//*/div[3]/div[4]/div/div[2]/div/div');
        I.click('//*/div[1]/div/div/div[4]/div/div[2]/span');
        I.click('//*/div[4]/div/div[3]/ul/li[1]/span');
        I.click('//*/div[5]/div/div/div[2]/span');
        I.click('//*/div[2]/form/div[1]/div/div/div[5]/div/div/div[3]/ul/li[1]/span');
        I.scrollPageToBottom();
        I.click('//*/div/div[1]/button');
      },

      createPurchaseOrder(){
        I.click('Create Purchase Order');
        I.click('//*//div[1]/div/div/div[1]/div/div/div/span');
        I.click('//*/div[1]/div/div/div[1]/div/div/div/ul/li[1]/span');
        I.click('//*/div[2]/div/div/input');
        I.click('//*/div/div/div[2]/div/div/div/div[2]/div//div[3]/div[4]//div[2]');
        I.click('//*/div[3]/div/div/input');
        I.click('//*/div[1]/div/div/div[3]//div[2]/div/div[3]/div/div/div[4]/div[4]/div/div[2]/div/div');
        I.click('//*/tbody/tr[1]/th/div/div[2]/span');
        I.click('//*/tbody/tr[1]/th/div/div[3]/ul/li[1]/span');
        I.scrollPageToBottom();
        I.click('//*/tfoot/tr/td//div[1]/button');
      },

      sales(){
        I.click('Sales');
        I.wait(5);
        I.click('.wperp-selected-option');
      },

      createInvoice(){
        I.click('Create Invoice');
        I.click('//form/div/div/div/div/div/div/div[2]');
        I.click('//div[3]/ul/li/span');
        I.click('//*/div[2]/div/div/input');
        I.click('//*/div/div/div[2]/div/div/div/div[2]/div/div[3]/div/div/div[3]/div[6]/div/div[2]/div/div');
        I.click('//*/div[3]/div/div/input');
        I.click('//*/div[1]/div/div/div[3]/div/div/div/div[2]/div/div[3]/div/div/div[4]/div[5]/div/div[2]/div/div');
        I.click('//th/div/div[2]');
        I.click('//th/div/div[3]/ul/li/span');
        I.click('//tfoot/tr/td/div/div/div');
      },

      recievePayment(){
        I.click('//*[@id="erp-accounting"]/div[2]/div[1]//ul/li[2]/a');
        I.click('//form/div/div/div/div/div/div[2]');
        I.click('//div[3]/ul/li/span');
        I.click('//*/div[3]/div/div/input');
        I.click('//*/div[5]/div[3]/div/div[2]/div/div');
        I.click('//form/div/div[4]/div/div[2]');
        I.click('//div[4]/div/div[3]/ul/li/span');
        I.click('//form/div/div[5]/div/div/div[2]');
        I.click('//div[5]/div/div/div[3]/ul/li/span/span');
        I.click('//tfoot/tr/td/div/div/div');
      },

      createEstimate(){
        I.click('Create Estimate');
        I.click('//form/div/div/div/div/div/div/div[2]')
        I.click('//div[3]/ul/li/span')
        I.click('//*/div[2]/div/div/input')
        I.click('//*/div[2]/div/div/div/div[2]/div/div[3]//div[4]/div[5]/div/div[2]/div/div')
        I.click('//*/div[3]/div/div/input')
        I.click('//*/div[3]//div/div/div[2]/div//div[5]/div[3]//div[2]/div/div')
        I.click('//th/div/div[2]')
        I.click('//th/div/div[3]/ul/li/span')
        I.click('//tfoot/tr/td/div/div/div')
      },

      Tax(){
        I.click('Tax Rates');
      },
      
      addTaxRate(){
        I.click('Add Tax Rate');
        I.wait(3);
        I.click('//*[@id="erp-accounting"]/div[2]/div[2]/div/form/div[1]/div[1]/div/div/div[2]');
        I.click('//div[3]/ul/li/span/span');
        I.fillField("(//input[@type='text'])[2]", 'Rinky_Automation');
        I.click('//td[2]/div');
        I.click('//td[2]/div/div[3]/ul/li/span');
        I.click("//div[@id='erp-accounting']/div[2]/div[2]/div/form/div[2]/table/tbody/tr/td[3]/div");
        I.click('//td[3]/div');
        I.wait(3);
        I.pressKey('Enter');  
        I.click('//*[@id="erp-accounting"]/div[2]/div[2]/div/form/div[2]/table/tbody/tr/td[4]/input');
        I.type('200');
        I.click('Save');
        I.wait(5);
      },

      addTaxZone(){
        I.click('View Tax Zones');
        I.click('Add Tax Zone');
        I.fillField('//*[@id="wperp-tax-agency-modal"]/div/div/form/div[1]/div[1]/input', 'Noakhali');
        I.fillField('//*[@id="wperp-tax-agency-modal"]/div/div/form/div[1]/div[2]/input','12345')
        I.click('Save')
      },

      addTaxCategory(){
        I.click('View Tax Categories');
        I.click('Add Tax Category');
        I.fillField('//input[@type="text"]', 'Standard');
        I.fillField('//textarea', 'Listing basic tax category');
        I.click('Save'); 
      },

      addTaxAgencies(){
        I.click('View Tax Agencies');
        I.click('Add Tax Agency');
        I.fillField('//input[@type="text"]', 'Agency');
        I.click('Save');
      },

      taxPayment(){
        I.click('Tax Payments');
        I.click('New Tax Payment');
        I.click('//div[@class="wperp-col-sm-4 with-multiselect"]//span[@class="multiselect__single"]');
        I.wait(2);
        I.click('//div[3]/ul/li/span');
        I.click('//span[@class="multiselect__placeholder"]');
        I.wait(2);
        I.click('//div[2]/div/div/div[3]/ul/li/span');
        I.click('//div[4]//div[1]//div[1]//div[2]//span[1]');
        I.wait(5);
        I.click('//div[4]/div/div/div[3]/ul/li/span/span');
        I.fillField('//input[@type="number"]', '120');
        I.click('Save');
        I.waitForElement('.app-customers', 30);
      },
}
