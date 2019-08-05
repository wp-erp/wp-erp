import axios from 'axios';

/* global erp_acct_var */
export default axios.create({
    baseURL: erp_acct_var.site_url + '/wp-json/erp/v1/accounting/v1',
    headers: {
        'X-WP-Nonce': erp_acct_var.rest_nonce
    }
});
