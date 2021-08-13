import axios from 'axios';

/* global for settings */
export default axios.create({
    baseURL: erp_settings_var.rest.root + erp_settings_var.rest.version + '/settings/v1',
    headers: {
        'X-WP-Nonce': erp_settings_var.rest.nonce
    }
});
