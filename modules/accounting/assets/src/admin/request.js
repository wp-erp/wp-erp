import store from './store/store'
import axios from "axios";
import Swal from 'sweetalert2'

const axiosConfig =  axios.create({
    baseURL: erp_acct_var.rest.root + erp_acct_var.rest.version + '/accounting/v1',
    headers: {
        'X-WP-Nonce': erp_acct_var.rest.nonce
    }
});

export const getRequest = function ( url, data = {}, silent = false ) {

    if (!silent) {
        store.dispatch('spinner/setSpinner', true);
    }

    return new Promise( ( resolve, reject ) => {

        store.dispatch('spinner/setSpinner', true);

        axiosConfig.get(url, { params: data } ).then( ( response ) => {

            store.dispatch( 'spinner/setSpinner', false );
            resolve( response.data );

        }).catch( errors => {

            if ( ! silent ) {
                store.dispatch('spinner/setSpinner', false);
                Swal({
                    position: 'center',
                    type: 'warning',
                    title: errors.response.data.message ? errors.response.data.message : errors.response.data,
                    showConfirmButton: false,
                    timer: 2500
                });
            }

            resolve( false );
        })

    })

}


export const postRequest = function ( url, data= {}, silent = false, multipart = false ) {

    if (!silent) {
        store.dispatch('spinner/setSpinner', true);
    }

    return new Promise((resolve, reject) => {

        store.dispatch('spinner/setSpinner', true);

        let header;
        if (multipart) {
            header = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        }

        axiosConfig.post( url,
            data,
            header
        ).then( response => {

            store.dispatch('spinner/setSpinner', false);

            resolve(response.data);

        }).catch( errors => {

            if ( ! silent ) {

                store.dispatch('spinner/setSpinner', false);

                Swal({
                    position: 'center',
                    type: 'warning',
                    title: errors.response.data.message ? errors.response.data.message : errors.response.data,
                    showConfirmButton: false,
                    timer: 2500
                });

            }

            resolve(false);
        })

    })

}
