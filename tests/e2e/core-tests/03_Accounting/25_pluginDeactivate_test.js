const helpers = require("../../pages/helpers");

Feature('DeactivePlugin');

Scenario('Deactivating WPERP Plugin',({ I, loginAs}) => {
    loginAs('admin');
    helpers.liteDeactivate();
    helpers.proDeactivate();
});
