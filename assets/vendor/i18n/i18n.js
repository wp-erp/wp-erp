import { setLocaleData, __, sprintf } from '@wordpress/i18n';

/* global erpLocale */
setLocaleData(erpLocale.locale_data, 'erp');

window.__ = __;
window.sprintf = sprintf;
