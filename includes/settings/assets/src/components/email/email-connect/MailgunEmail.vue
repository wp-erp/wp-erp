<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="mailgun"
        :enable_content="true"
        :enableSubSectionTitle="false"
        :disableMenu="true"
        :disableSectionTitle="true"
        :options="options">
        <div slot="extended-data">
            <slot name="extended-data">
                <div class="wperp-form-group test-connection">
                    <div class="connection-outgoing">
                        <label for="mailgun_test_email_address">{{ __( 'Test Mail', 'erp' ) }}</label>
                        <p>{{ __( 'An Email Address to Test the Connection', 'erp' ) }}</p>
                        <input
                            class="wperp-form-field"
                            :placeholder="__( 'Email here', 'erp' )"
                            id="mailgun_test_email_address"
                        />
                        <button id="mailgun-test-connection" class="wperp-btn btn--secondary btn-test-connection" @click="testConnection">{{ __('Send Test Email', 'erp') }}</button>
                    </div>
                </div>
            </slot>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: 'MailgunEmail',

    components: {
        BaseLayout,
    },

    data() {
        return {
            mailgunTestEmail: '',
            options: {},
        }
    },

    methods: {
        testConnection() {
            this.options.action    = 'erp_mailgun_test_connection';
            this.options.recurrent = false;
        }
    }
}
</script>
