<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="email_connect"
        sub_sub_section_id="imap"
        :enable_content="true"
        :single_option="false"
    >

    <template slot="extended-data">
        <div style="margin-top: 10px; margin-bottom: 30px">
            <a class="button-secondary" @click="testImapConnection" v-html="imapTestString"></a>
            <p class="erp-form-input-hint">
                {{ __('Click on the above button before saving the settings.', 'erp') }}
            </p>
        </div>
    </template>

    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

var $ = jQuery;

export default {
    name: "CrmEmailConnectImap",

    components: {
        BaseLayout
    },

    data() {
        return {
            imapTestString: __('Test Connection', 'erp')
        }
    },

    methods: {

        /**
         * Test imap connection settings can create valid connection or not
         */
        testImapConnection() {
            const self = this;
            self.imapTestString = 'Testing Connection <i class="fa fa-spinner fa-spin"></i>';

            const data = {
                'action'        : 'erp_imap_test_connection',
                'mail_server'   : $('#erp-mail_server').val(),
                'username'      : $('#erp-username').val(),
                'password'      : $('#erp-password').val(),
                'protocol'      : $('#erp-protocol').val(),
                'port'          : $('#erp-port').val(),
                'authentication': $('#erp-authentication').val(),
                '_wpnonce'      : erp_settings_var.nonce
            }

            $.post( erp_settings_var.ajax_url, data, function ( response ) {
                const type = response.success ? 'success' : 'error';

                if ( response.data ) {
                    const status = response.success ? 1 : 0;

                    self.$emit('inputImapStatus', 'imap_status')

                    self.imapTestString = __('Test Connection', 'erp');

                    swal({
                        title: '',
                        text: response.data,
                        type: type,
                        confirmButtonText: __('OK', 'erp'),
                        confirmButtonColor: '#008ec2'
                    });
                }
            });
        }
    },
};
</script>
