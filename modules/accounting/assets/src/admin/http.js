import axios from 'axios';

/* global erp_acct_var */
export default axios.create({
    baseURL: erp_acct_var.rest.root + erp_acct_var.rest.version + '/accounting/v1',
    headers: {
        'X-WP-Nonce': erp_acct_var.rest.nonce
    }
});
