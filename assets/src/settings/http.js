import axios from 'axios';

/* global erp_settings_var */
export default axios.create({
    baseURL: erp_settings_var.rest.root + erp_settings_var.rest.version + '/accounting/v1',
    headers: {
        'X-WP-Nonce': erp_settings_var.rest.nonce
    }
});
