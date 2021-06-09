import { setLocaleData, __, sprintf } from '@wordpress/i18n';

/* global erpSettings */
setLocaleData(erpSettings.locale_data, 'erp');

// hook other add-on locale
window.settings_add_locale = function(name, localeData) {
    setLocaleData(localeData, name);
};

window.__ = __;
window.sprintf = sprintf;
