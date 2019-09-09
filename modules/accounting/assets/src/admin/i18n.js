import { setLocaleData, __, sprintf } from '@wordpress/i18n';

/* global erpAcct */
setLocaleData(erpAcct.locale_data, 'erp');

// hook other add-on locale
window.acct_add_locale = function(name, localeData) {
    setLocaleData(localeData, name);
};

window.__ = __;
window.sprintf = sprintf;
