<template>
    <div id="erp-integration">
        <base-layout
            :section_id="section"
            :sub_section_id="section">   
            
            <table class="erp-settings-table widefat">
                <thead>
                    <tr>
                        <th v-for="(column, index) in columns" :key="index">
                            {{ column }}
                        </th>
                    </tr>
                </thead>

                <tbody v-if="Object.keys(integrations).length">
                    <tr valign="top" v-for="(item, key) in integrations" :key="key">
                        <td>
                            <span
                                class="integration-title"
                                @click="configure(item, key)">
                                {{ item.title }}
                            </span>
                        </td>
                        <td>{{ item.description }}</td>
                        <td>
                            <button
                                class="wperp-btn btn--primary"
                                @click="configure(item, key)"
                                :id="item.id">
                                {{ __('Configure', 'erp') }}
                            </button>
                        </td>
                    </tr>
                </tbody>
                
                <tbody v-else>
                    <tr :col-span="numColumns">
                        <th>{{ __('No templates found.', 'erp') }}</th>
                    </tr>
                </tbody>
            </table>

        </base-layout>
    </div>
</template>

<script>
import Modal from '../base/Modal.vue';
import BaseLayout from '../layouts/BaseLayout.vue';
import SubmitButton from '../base/SubmitButton.vue';
import MultiSelect from '../select/MultiSelect.vue';
import BaseContentLayout from '../layouts/BaseContentLayout.vue';

export default {
    name: 'Integration',

    components: {
        BaseLayout,
    },

    data() {
        return {
            section       : 'erp-integration',
            subSection    : '',
            integrations  : {},
            singleItem    : {},
            showModal     : false,
            subSubSection : '',
            componentKey  : 0,
            options       : {},
            fieldOptions  : {},
            selectedField : {
                id   : '',
                name : '',
            },
            columns       : [ 
                __('Integration', 'erp'),
                __('Description', 'erp'),
                ''
            ],
        }
    },

    created() {
        let section       = erp_settings_var.erp_settings_menus.find(menu => menu.id === this.section);
        this.integrations = section.extra.integrations;
    },

    computed: {
        numColumns() {
            return this.columns.length;
        },

        formFields() {
            return this.selectedField
                && this.singleItem.form_fields[ this.selectedField.id ] !== undefined
                ? this.singleItem.form_fields[ this.selectedField.id ]
                : this.singleItem.form_fields;
        }
    },

    methods: {
        configure(item, key) {
            this.singleItem = item;
            this.subSection = key;
            this.showModal  = true;
        },
    }
}
</script>