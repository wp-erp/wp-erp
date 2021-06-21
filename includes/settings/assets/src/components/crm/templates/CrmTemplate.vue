<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="templates"
        :enable_content="false"
        :single_option="false"
    >
        <template v-slot:subSectionTitle>
            {{ __('Saved Replies', 'erp') }}
            <button type="button" class="wperp-btn btn--primary settings-button" @click="popupModal({}, 'create')">
                <i class="fa fa-plus"></i> {{ __('Add New', 'erp') }}
            </button>
        </template>

        <table class="erp-settings-table widefat">
            <thead>
                <tr>
                    <th>{{ __('Template Name', 'erp') }}</th>
                    <th>{{ __('Subject', 'erp') }}</th>
                    <th>{{ __('Enable/Disable', 'erp') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr valign="top" v-for="(template, index) in templates" :key="index">
                    <td>{{ template.name }}</td>
                    <td>{{ template.subject }}</td>
                    <td>
                        <span @click="popupTemplate(template, true)" class="action"><i class="fa fa-pencil"></i></span>
                        <span @click="deleteTemplate(template.id)" class="action"><i class="fa fa-trash"></i></span>
                    </td>
                </tr>
            </tbody>
        </table>

    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import SubmitButton from "../../base/SubmitButton.vue";
import { generateFormDataFromObject } from "../../../utils/FormDataHandler";
var $ = jQuery;

export default {
    name: "CrmTemplate",

    components: {
        BaseLayout,
        SubmitButton
    },

    data() {
        return {
            templates: []
        }
    },

    created() {
        this.getTemplatesData();
    },

    methods: {

        /**
         * Get template lists
         */
        getTemplatesData() {
            const self = this;

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    _wpnonce: erp_settings_var.nonce,
                    action  : "erp-crm-get-save-replies",
                }
            );

            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success    : function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        self.templates = response.data;
                    }
                },
            });
        },

        /**
         * Popup template modal for create & edit
         */
        popupTemplate( template, isEdit = false) {

        },

        /**
         * Delete Template
         */
        deleteTemplate( templateId ) {

        },
    },


};
</script>
