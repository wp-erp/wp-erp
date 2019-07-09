import { setLocaleData, __, sprintf } from '@wordpress/i18n';

setLocaleData(erpAcct.locale_data, 'erp');

window.__ = __;
window.sprintf = sprintf;
