const { helper } = require("codeceptjs");
const helpers = require("../../pages/helpers");

Feature('ActivePlugin');
Scenario('@HRM Activating WPERP Plugin',async ({ I, loginAs}) => {
    loginAs('admin');
    I.amOnPage('/wp-admin/plugins.php');
    const deactivate = await I.grabNumberOfVisibleElements('#deactivate-erp');
        if (deactivate >= 1) {
            I.see('WP ERP');
        }
        else{
            helpers.liteActivate();
        }
    });
