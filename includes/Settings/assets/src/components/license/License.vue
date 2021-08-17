<template>
    <div id="erp-license">
        <base-layout
            :section_id="section"
            :sub_section_id="section">
            
            <form @submit.prevent="saveSettings">
                <table class="erp-settings-table widefat">
                    <thead>
                        <tr>
                            <th v-for="(column, index) in columns" :key="index">
                                {{ column }}
                            </th>
                        </tr>
                    </thead>

                    <tbody v-if="extensions">
                        <tr valign="top" v-for="(item, key) in extensions" :key="key">
                            <td><strong>{{ item.name }}</strong></td>
                            <td>{{ item.version }}</td>
                            <td><input type="text" v-model="item.license" /></td>
                            <td v-html="item.status"></td>
                        </tr>
                    </tbody>
                    
                    <tbody v-else>
                        <tr :col-span="numColumns">
                            <th>{{ __( 'No extensions found.', 'erp' ) }}</th>
                        </tr>
                    </tbody>
                </table>

                <div class="wperp-form-group">
                    <submit-button :text="__( 'Save Changes', 'erp' )" />
                    <div class="clearfix"></div>
                </div>
            </form>
        </base-layout>
    </div>
</template>

<script>
import BaseLayout from '../layouts/BaseLayout.vue';
import SubmitButton from '../base/SubmitButton.vue';

export default {
    name: 'License',

    components: {
        BaseLayout,
        SubmitButton
    },

    data() {
        return {
            section    : 'erp-license',
            extensions : {},
            columns    : [ 
                __( 'Extension', 'erp' ),
                __( 'Version', 'erp' ),
                __( 'License Key', 'erp' ),
                __( 'Status', 'erp' ),
            ],
        }
    },

    created() {
        let section     = erp_settings_var.erp_settings_menus.find(menu => menu.id === this.section);
        this.extensions = section.extra.extensions;
    },

    computed: {
        numColumns() {
            return this.columns.length;
        },
    },

    methods: {
        saveSettings() {
            this.$store.dispatch('spinner/setSpinner', true);

            var self = this;

            let data = {
                extensions : this.extensions,
                _wpnonce   : erp_settings_var.nonce,
                action     : 'erp_settings_save_licenses'
            }

            wp.ajax.send({
                data : data,
                success: function( response ) {
                    self.$store.dispatch('spinner/setSpinner', false);
                    self.showAlert('success', response);
                },
                error: function( error ) {
                    self.$store.dispatch('spinner/setSpinner', false);
                    self.showAlert('error', error);
                }
            });
        }
    }
}
</script>
