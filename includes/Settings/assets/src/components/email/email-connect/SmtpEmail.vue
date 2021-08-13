<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="smtp"
        :enable_content="true"
        :enableSubSectionTitle="false"
        :disableMenu="true"
        :disableSectionTitle="true"
        :options="options">
        <div slot="extended-data">
            <slot name="extended-data">
                <div class="wperp-form-group test-connection">
                    <div class="connection-outgoing">
                        <label for="smtp_test_email_address">{{ __( 'Test Mail', 'erp' ) }}</label>
                        <p>{{ __( 'An Email Address to Test the Connection', 'erp' ) }}</p>
                        <input
                            class="wperp-form-field"
                            :placeholder="__( 'Email here', 'erp' )"
                            id="smtp_test_email_address"
                            v-model="smtpTestEmail"
                        />
                        <button id="smtp-test-connection" class="wperp-btn btn--secondary btn-test-connection" @click="testConnection">{{ __( 'Send Test Email', 'erp' ) }}</button>
                    </div>
                </div>
            </slot>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: 'SmtpEmail',

    components: {
        BaseLayout,
    },

    data() {
        return {
            smtpTestEmail: '',
            options      : {
                action   : '',
                recurrent: false,
                fields   : []
            }
        }
    },

    methods: {
        testConnection() {
            this.options.action = 'erp_smtp_test_connection';

            this.options.fields.push(
                {
                    'key'   : 'test_email',
                    'value' : this.smtpTestEmail
                }
            );
        }
    }
}
</script>
