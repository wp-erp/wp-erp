<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="smtp"
        :enable_content="true"
        :options="options">
        <div slot="extended-data">
            <slot name="extended-data">
                <div class="wperp-form-group">
                    <label for="smtp-test-connection"></label>
                    
                    <button id="smtp-test-connection" class="wperp-btn btn--secondary" @click="testConnection">{{ __('Send Test Email', 'erp') }}</button>
                </div>
            </slot>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: 'SMTPEmail',

    components: {
        BaseLayout,
    },

    data() {
        return {
            smtpTestEmail: '',
            options: {},
        }
    },

    methods: {
        testConnection() {
            this.options.action    = 'erp_smtp_test_connection';
            this.options.recurrent = false;
        }
    }
}
</script>
