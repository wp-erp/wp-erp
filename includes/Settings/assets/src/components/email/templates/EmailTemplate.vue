<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="templates"
        :enable_content="false"
        :single_option="false"
        :enableSubSectionTitle="false"
    >
        <h3 class="sub-section-title pull-left">{{ __('Saved Replies', 'erp') }}</h3>
        <button type="button" class="wperp-btn btn--primary settings-button header-right-button" @click="popupModal({}, 'create')">
            <i class="fa fa-plus"></i> {{ __('Add New', 'erp') }}
        </button>
        <div class="clearfix"></div>

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
                        <span @click="popupModal(template, 'edit')" class="action"><i class="fa fa-pencil"></i></span>
                        <span @click="popupDeleteModal(template)" class="action"><i class="fa fa-trash"></i></span>
                    </td>
                </tr>
            </tbody>
        </table>

         <modal
            v-show="isVisibleModal"
            :title="modalMode === 'create' ? __('Add new Template', 'erp') : __('Edit', 'erp')"
            @close="popupModal({}, modalMode)"
            :header="true"
            :footer="true"
            :hasForm="true"
        >
            <template v-slot:body>
                <form class="wperp-form" method="post" @submit.prevent="onFormSubmit">
                    <div class="wperp-form-group">
                        <label>{{ __('Name', 'erp') }} <span class="required">*</span></label>
                        <input v-model="singleTemplate.name" class="wperp-form-field" required />
                    </div>

                    <div class="wperp-form-group">
                        <label>{{ __('Subject', 'erp') }}</label>
                        <input v-model="singleTemplate.subject" class="wperp-form-field" />
                    </div>

                    <div style="margin-bottom: 60px;">
                        <div class="wperp-form-group" style="clear: both; position: absolute; right: 18px;">
                            <label>{{ __('Short Codes', 'erp') }}</label>
                            <select
                                v-model="singleTemplate.shortCode" class="wperp-form-field"
                                @change="appendShortCode"
                            >
                                <option v-for="(shortCode, key, index) in shortCodes" :key="index">
                                    {{ key }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="wperp-form-group">
                        <label>{{ __('Body', 'erp') }}</label>
                        <VueTrix v-model="singleTemplate.template" placeholder="Enter content" localStorage/>
                    </div>

                </form>
            </template>

            <template v-slot:footer>
                <span @click="onFormSubmit">
                    <submit-button :text="modalMode === 'create' ? __('Add New', 'erp') : __('Save', 'erp') " customClass="pull-right" />
                </span>

                <span @click="popupModal({}, modalMode)">
                    <submit-button :text="__('Cancel', 'erp')" customClass="wperp-btn-cancel pull-right" style="margin-right: 10px" />
                </span>
            </template>
        </modal>


    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import SubmitButton from "../../base/SubmitButton.vue";
import Modal from '../../base/Modal.vue';
import VueTrix from "vue-trix";
import { generateFormDataFromObject } from "../../../utils/FormDataHandler";

var $ = jQuery;

export default {
    name: "EmailTemplate",

    components: {
        BaseLayout,
        SubmitButton,
        Modal,
        VueTrix
    },

    data() {
        return {
            templates     : [],
            isVisibleModal: false,
            singleTemplate: {},
            shortCodes    : [],
            modalMode     : 'create' // 'create' or 'edit'
        }
    },

    created() {
        this.$store.dispatch("spinner/setSpinner", true);
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
                        self.templates  = response.data.replies;
                        self.shortCodes = response.data.short_codes;
                    }
                },
            });
        },

        /**
         * Template Saving for create and edit
         */
        onFormSubmit() {
            const self     = this;
            const isUpdate = self.modalMode === 'edit' ? true : false;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = {
                ...self.singleTemplate,
                id       : self.modalMode === 'edit' ? self.singleTemplate.id : 0,
                action   : ! isUpdate ? 'erp-crm-save-replies' : 'erp-crm-edit-save-replies',
                _wpnonce : wpErpCrm.nonce
            };

            requestData    = window.settings.hooks.applyFilters( "requestData", requestData );
            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        if ( isUpdate ) {
                            self.singleTemplate = {};
                            self.popupModal({}, 'edit');
                        } else {
                            self.popupModal({}, 'create');
                        }

                        self.getTemplatesData();
                        self.showAlert("success", response.data.message);
                    } else {
                        self.showAlert("error", response.data);
                    }
                }
            });
        },

        /**
         * Popup template modal for create & edit
         */
        popupModal( template, modalMode ) {
            if ( this.isVisibleModal ) {
                this.isVisibleModal = false;
            } else {
                this.isVisibleModal = true;
            }

            this.singleTemplate = modalMode === 'create' ? {} : template;
            this.modalMode      = modalMode;
        },

        /**
         * Popup Delete modal and
         * On confirmation of deletion, delete
         */
        popupDeleteModal( template ) {
            const self = this;

            swal({
                title             : __('Delete', 'erp'),
                text              : __('Are you sure to delete this ?', 'erp'),
                type              : "warning",
                showCancelButton  : true,
                cancelButtonText  : __('Cancel', 'erp'),
                confirmButtonColor: "#DD6B55",
                confirmButtonText  : __('Delete', 'erp'),
                closeOnConfirm    : false
            },
            function() {
                $.ajax({
                    type    : "POST",
                    url     : erp_settings_var.ajax_url,
                    dataType: 'json',
                    data    : {
                        id      : template.id,
                        _wpnonce: wpErpCrm.nonce,
                        action  : 'erp-crm-delete-save-replies'
                    },
                } )
                .fail( function( xhr ) {
                    self.showAlert('error', xhr);
                } )
                .done( function( response ) {
                    swal.close();

                    if ( response.success ) {
                        self.showAlert('success', response.data.message);
                        self.getTemplatesData();
                    } else {
                        self.showAlert('error', response.data);
                    }
                });
            });
        },

        /**
         * Append short code in template body description box
         */
        appendShortCode() {
            const templateText           = typeof this.singleTemplate.template === 'undefined' ? '': this.singleTemplate.template;
            this.singleTemplate.template = templateText + this.singleTemplate.shortCode;
        }
    }
};
</script>
