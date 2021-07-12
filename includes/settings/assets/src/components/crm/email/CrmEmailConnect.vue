<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="email_connect"
        :enable_content="true"
        :single_option="false"
    >
        <template slot="extended-data">
            <table class="erp-settings-table widefat" style="margin-top: 10px; margin-bottom: 30px">
                <thead>
                    <tr>
                        <th>{{ __('Provider', 'erp') }}</th>
                        <th>{{ __('Description', 'erp') }}</th>
                        <th>{{ __('Status', 'erp') }}</th>
                        <th>{{ __('Action', 'erp') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr valign="top" v-for="(provider, key, index) in providers" :key="index">
                        <td>{{ provider.name }}</td>
                        <td>{{ provider.description }}</td>
                        <td>{{ provider.enabled ? __('Enabled', 'erp') : __('Disabled', 'erp') }}</td>
                        <td>
                            <router-link :to="`/${section_id}/${sub_section_id}/${key}`" class="button">
                                <span>{{ __('Settings', 'erp') }}</span>
                            </router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "CrmEmailConnect",

    components: {
        BaseLayout
    },

    data() {
        return {
            providers     : [],
            sub_section_id: 'email_connect',
            section_id    : 'erp-crm'
        }
    },

    created() {
        this.getProviders();
    },

    methods: {

        /**
        * Get Email Providers List
        **/
        getProviders() {
            const menus         = erp_settings_var.erp_settings_menus;
            const parentMenu    = menus.find( menu => menu.id === this.section_id );
            const emailConnects = parentMenu['fields'][this.sub_section_id];

            emailConnects.map( emailConnect => {
                if ( emailConnect.type === 'sub_sections' ) {
                    this.providers = emailConnect.sub_sections;
                }
            } );
        }
    },
};
</script>
