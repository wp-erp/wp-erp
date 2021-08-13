<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="imap"
        :enable_content="true"
        :enableSubSectionTitle="false"
        :disableMenu="true"
        :disableSectionTitle="true"
    >

    <template slot="extended-data">
        <div class="test-connection">
            <div class="connection-incoming">
                <label>{{ __( 'Test Connection', 'erp' ) }}</label>
                <p>{{ __( 'Click on the Above Button Before Saving the Setting', 'erp' ) }}</p>

                <button type="button" class="wperp-btn btn--secondary btn-test-connection" @click="testImapConnection" v-html="imapTestString"></button>
            </div>
        </div>
    </template>

    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

var $ = jQuery;

export default {
    name: "ImapEmail",

    components: {
        BaseLayout
    },

    data() {
        return {
            imapTestString: __( 'Test Connection', 'erp' )
        }
    },

    methods: {

        /**
         * Test imap connection settings can create valid connection or not
         */
        testImapConnection() {
            const self = this;
            self.imapTestString = __( 'Testing Connection', 'erp' ) + ' <i class="fa fa-spinner fa-spin"></i>';

            const data = {
                'action'        : 'erp_imap_test_connection',
                'mail_server'   : $('#erp-settings-box-erp-email-imap #erp-mail_server').val(),
                'username'      : $('#erp-settings-box-erp-email-imap #erp-username').val(),
                'password'      : $('#erp-settings-box-erp-email-imap #erp-password').val(),
                'protocol'      : $('#erp-settings-box-erp-email-imap #erp-protocol').val(),
                'port'          : $('#erp-settings-box-erp-email-imap #erp-port').val(),
                'authentication': $('#erp-settings-box-erp-email-imap #erp-authentication').val(),
                '_wpnonce'      : erp_settings_var.nonce
            }

            $.post( erp_settings_var.ajax_url, data, function ( response ) {
                const type = response.success ? 'success' : 'error';

                if ( response.data ) {
                    self.$emit( 'inputImapStatus', 'imap_status' )

                    self.imapTestString = __( ' Test Connection', 'erp' );

                    swal({
                        title: '',
                        text: response.data,
                        type: type,
                        confirmButtonText: __( ' OK', 'erp' ),
                        confirmButtonColor: '#008ec2'
                    });
                }
            });
        }
    },
};
</script>
