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

            <modal
                v-if="showModal"
                :title="singleItem.title + __(' Integration', 'erp')"
                @close="toggleModal"
                :header="true"
                :footer="true"
                :hasForm="true">

                <template v-slot:body>
                    <div class="wperp-form-group" v-if="singleItem.id === 'erp-sms'">
                        <label for="erp-sms-selected-gateway">{{ __( 'Active Gateway', 'erp' ) }}</label>

                        <multi-select
                            v-model="selectedField"
                            :options="fieldOptions"
                            :multiple="false"
                            @select="onSelect"
                            id="erp-sms-selected-gateway"/>
                    </div>

                    <base-content-layout
                        ref="base"
                        :key="componentKey"
                        :section_id="section"
                        :sub_section_id="subSection"
                        :sub_sub_section_id="singleItem.id"
                        :inputs="formFields"
                        :single_option="singleItem.single_option"
                        :hide_submit="true"
                        :options="options">

                        <div slot="extended-data" v-if="extraContent">
                            <slot name="extended-data">
                                <div class="wperp-form-group" v-if="singleItem.id === 'erp-dm'">
                                    <label for="dropbox-connection-test"></label>
                                    <button id="dropbox-connection-test"
                                        class="wperp-btn btn--secondary"
                                        @click="testConnection">
                                        {{ __('Test Dropbox Connection', 'erp') }}
                                    </button>
                                </div>
                            </slot>
                        </div>
                    </base-content-layout>
                </template>

                <template v-slot:footer>
                    <span @click="onSubmit" v-if="! hideSubmit">
                        <submit-button
                            :text="__('Save', 'erp')"
                            customClass="pull-right" />
                    </span>

                    <span @click="toggleModal">
                        <submit-button
                            :text="__('Cancel', 'erp')"
                            customClass="wperp-btn-cancel pull-right"
                            style="margin-right: 7px;" />
                    </span>
                </template>
            </modal>
        </base-layout>
    </div>
</template>

<script>
import Modal from '../base/Modal.vue';
import BaseLayout from '../layouts/BaseLayout.vue';
import SubmitButton from '../base/SubmitButton.vue';
import MultiSelect from '../select/MultiSelect.vue';
import BaseContentLayout from '../layouts/BaseContentLayout.vue';
import { mapState } from 'vuex';

export default {
    name: 'Integration',

    components: {
        Modal,
        BaseLayout,
        MultiSelect,
        SubmitButton,
        BaseContentLayout,
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
            options       : {
                action   : '',
                recurrent: false,
            },
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

    watch: {
        selectedField(newVal) {
            if (newVal.id) {
                this.forceUpdateBody();
            }
        },

        formDatas: function(formData) {
            if ( typeof formData !== 'undefined' && formData !== null ) {
                this.testConnection();
            }
        }
    },

    computed: {
        numColumns() {
            return this.columns.length;
        },

        extraContent() {
            return this.singleItem.id === 'erp-dm';
        },

        hideSubmit() {
            return this.singleItem.id === 'salesforce-integration';
        },

        formFields() {
            return this.selectedField
                && this.singleItem.form_fields[ this.selectedField.id ] !== undefined
                ? this.singleItem.form_fields[ this.selectedField.id ]
                : this.singleItem.form_fields;
        },

        ...mapState({
            formDatas( state ) {
                return state.formdata.data;
            }
        })
    },

    methods: {
        configure(item, key) {
            if ( key === 'mailchimp' ) {
                this.$router.push({
                    name: 'MailchimpSettings',
                });
                return;
            }

            this.singleItem = item;
            this.subSection = key;

            if (key === 'sms') {
                this.selectedField.id   = item.extra.selected_gateway;
                this.selectedField.name = item.sections[this.selectedField.id];
                this.fieldOptions       = item.sections;
            }

            this.showModal  = true;
        },

        toggleModal() {
            this.showModal = false;
        },

        onSubmit() {
            this.options.action = '';
            this.$refs.base.onFormSubmit();
        },

        forceUpdateBody() {
            this.componentKey += 1;
        },

        onSelect(selected) {
            this.selectedField = selected;
        },

        testConnection() {
            let options = Object.assign({}, options);
            options.action = 'wp-erp-sync-employees-dropbox';
            this.options = options;
        }
    }
}
</script>
