import axios from 'axios'

export const HTTP = axios.create({
    baseURL: acc_var.site_url+'/wp-json/erp/v1',
    headers: {
        'X-WP-Nonce':  acc_var.rest_nonce
    }
});
