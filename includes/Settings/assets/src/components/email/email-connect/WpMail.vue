<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="wpmail"
        :enable_content="true"
        :enableSubSectionTitle="false"
        :disableMenu="true"
        :disableSectionTitle="true"
        :options="options">
        <div slot="extended-data">
            <slot name="extended-data">
                <div class="wperp-form-group test-connection">
                    <div class="connection-outgoing">
                        <label for="erp_wpmail_test_email">{{ __( 'Test Mail', 'erp' ) }}</label>
                        <p>{{ __( 'An Email Address to Test the Connection', 'erp' ) }}</p>
                        <input
                            class="wperp-form-field"
                            :placeholder="__( 'Email here', 'erp' )"
                            id="erp_wpmail_test_email"
                            type="email"
                            v-model="erp_wpmail_test_email"
                        />
                        <button id="wpmail-test-connection" class="wperp-btn btn--secondary btn-test-connection" @click="testConnection">{{ __('Send Test Email', 'erp') }}</button>
                    </div>
                </div>
            </slot>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: 'WpMail',

    components: {
        BaseLayout,
    },

    data() {
        return {
            erp_wpmail_test_email: '',
            options               : {
                action   : '',
                recurrent: false,
                fields   : []
            }
        }
    },

    methods: {
        testConnection() {
            this.options.action = 'erp_wpmail_test_connection';
            
            this.options.fields.push(
                {
                    'key'   : 'erp_wpmail_test_email',
                    'value' : this.erp_wpmail_test_email
                }
            );
        }
    }
}
</script>
