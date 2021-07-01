<template>
    <base-layout
        :section_id="section"
        :sub_section_id="subSection"
        :enable_content="false"
    >
        <div>
            <ul class="sub-sub-menu">
                <li v-for="(menu, key, index) in options.sub_sections" :key="key">
                    <a :class="key === subMenu? 'router-link-active': ''" @click="setSubMenu(key)">
                        <span class="menu-name">{{ menu }}</span>
                    </a>
                </li>
            </ul>

            <table class="erp-settings-table widefat">
                <thead>
                    <tr>
                        <th>{{ __('Template Name', 'erp') }}</th>
                        <th>{{ __('Description', 'erp') }}</th>
                        <th>{{ __('Disable/Enable', 'erp') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody v-if="emails">
                    <tr valign="top" v-for="(email, index) in emails" :key="index">
                        <td>{{ email.name }}</td>
                        <td>{{ email.description }}</td>
                        <td>
                            <radio-switch
                                v-if="email.disable_allowed"
                                :value="email.is_enabled"
                                :id="email.id"
                                @click.native="toggleStatus(email, index)"
                            ></radio-switch>
                        </td>
                        <td>
                            <button
                                class="wperp-btn btn-secondary"
                                @click="configureTemplate(email.id)"
                                :id="email.option_id">
                                {{ __('Configure', 'erp') }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <modal
            v-if="showModal"
            :title="__('Configure Template', 'erp')"
            @close="toggleModal"
            :header="true"
            :footer="true"
            :hasForm="true"
        >
            <template v-slot:body v-if="singleTemplate">
                <h4>{{ singleTemplate.title }}</h4>
                <p>{{ singleTemplate.description }}</p>
                <form class="wperp-form" method="post" @submit.prevent="onSubmit">
                    <div class="wperp-form-group" v-if="singleTemplate.disable_allowed">
                        <label>{{ __('Enable/Disable', 'erp') }}</label>
                        <radio-switch
                            v-model="singleTemplate.is_enable"
                            :id="singleTemplate.id"
                            @click.native="switchValue"
                        ></radio-switch>
                    </div>

                    <div class="wperp-form-group">
                        <label>{{ __('Subject', 'erp') }}</label>
                        <input v-model="singleTemplate.subject" class="wperp-form-field" />
                    </div>

                    <div class="wperp-form-group">
                        <label>{{ __('Heading', 'erp') }}</label>
                        <input v-model="singleTemplate.heading" class="wperp-form-field" />
                    </div>

                    <div class="wperp-form-group">
                        <label>{{ __('Body', 'erp') }}</label>
                        <VueTrix v-model="singleTemplate.body" placeholder="Enter content" localStorage/>
                    </div>

                    <div class="wperp-form-group" v-if="shortCodes.length">
                        <label>
                            <span>{{ __('Template Tags', 'erp') }}</span>
                            <tooltip
                                :input="{
                                    tooltip: true,
                                    tooltip_text: __('You may use these template tags inside subject, heading, body and those will be replaced by original values', 'erp') 
                                }"
                            />
                        </label>

                        <div>{{ arrayToString(shortCodes, ', ') }}</div>
                    </div>
                </form>
            </template>

            <template v-slot:footer>
                <span @click="toggleModal">
                    <submit-button :text="__('Cancel', 'erp')" customClass="wperp-btn-cancel"/>
                </span>

                <span @click="onSubmit">
                    <submit-button :text="__('Save', 'erp')" />
                </span>
            </template>
        </modal>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import RadioSwitch from "../../layouts/partials/Switch.vue";
import SubmitButton from "../../base/SubmitButton.vue";
import Modal from '../../base/Modal.vue';
import VueTrix from "vue-trix";
import Tooltip from '../../base/Tooltip.vue';
import { generateFormDataFromObject } from "../../../utils/FormDataHandler";

export default {
    name: "EmailNotification",

    components: {
        BaseLayout,
        RadioSwitch,
        Modal,
        VueTrix,
        SubmitButton,
        Tooltip,
    },

    data() {
        return {
            section         : "erp-email",
            subSection      : "notification",
            subSectionTitle : "",
            options         : [],
            content         : '',
            emailTemplates  : [],
            singleTemplate  : [],
            shortCodes      : [],
            subMenu         : 'hrm',
            showModal       : false,
        }
    },

    created() {
        const menuItems      = erp_settings_var.erp_settings_menus;
        const parentMenu     = menuItems.find(menu => menu.id === this.section);

        this.subSectionTitle = parentMenu.sections[ this.subSection ];
        this.options         = parentMenu.fields[ this.subSection ];

        this.getEmailTemplates();
    },

    computed: {
        emails() {
            return this.emailTemplates ? this.emailTemplates[ this.subMenu ] : [];
        }
    },

    methods: {
        getEmailTemplates() {
            var self = this;
            
            self.$store.dispatch("spinner/setSpinner", true);

            wp.ajax.send({
                data : {
                    action   : "erp_get_email_templates",
                    _wpnonce : erp_settings_var.nonce,
                },
                success: function(response) {
                    self.emailTemplates = response;
                    self.$store.dispatch("spinner/setSpinner", false);
                },
                error: function(error) {
                    self.showAlert("error", error);
                    self.$store.dispatch("spinner/setSpinner", false);
                }
            });
        },

        toggleStatus(email, index) {
            var self   = this,
                status = email.is_enabled === 'yes' ? 'no' : 'yes';
            
            self.$store.dispatch("spinner/setSpinner", true);

            wp.ajax.send({
                data : {
                    option_id    : email.option_id,
                    option_value : status,
                    action       : "erp_update_email_status",
                    _wpnonce     : erp_settings_var.nonce,
                },
                success: function(response) {
                    self.$set(self.emailTemplates[self.subMenu][index], 'is_enabled', status);
                    self.$store.dispatch("spinner/setSpinner", false);
                },
                error: function(error) {
                    self.showAlert("error", error);
                    self.$store.dispatch("spinner/setSpinner", false);
                }
            });
        },

        configureTemplate(template) {
            var self = this;

            self.showModal = true;

            wp.ajax.send({
                data : {
                    template : template,
                    action   : "erp_get_single_email_template",
                    _wpnonce : erp_settings_var.nonce,
                },
                success: function(response) {
                    self.singleTemplate = response;
                    self.shortCodes     = response.tags;
                    self.$store.dispatch("spinner/setSpinner", false);
                },
                error: function(error) {
                    self.showAlert("error", error);
                    self.$store.dispatch("spinner/setSpinner", false);
                }
            });
        },

        onSubmit() {
            var self = this;

            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = {
                id        : self.singleTemplate.id,
                is_enable : self.singleTemplate.is_enable,
                subject   : self.singleTemplate.subject,
                heading   : self.singleTemplate.heading,
                body      : self.singleTemplate.body,
                action    : 'erp_update_email_template',
                _wpnonce  : erp_settings_var.nonce,
            };

            requestData    = window.settings.hooks.applyFilters( "requestData", requestData );
            const postData = generateFormDataFromObject(requestData);

            wp.ajax.send({
                data    : requestData,
                success : function(response) {
                    self.$store.dispatch("spinner/setSpinner", false);
                    self.showModal = false;
                    self.getEmailTemplates();
                    self.showAlert("success", response);
                },
                error   : function(error) {
                    self.$store.dispatch("spinner/setSpinner", false);
                    self.showAlert("error", response.data);
                }
            });
        },

        toggleModal() {
            this.showModal = ! this.showModal;
        },

        switchValue() {
            let newValue = this.singleTemplate.is_enable === 'yes' ? 'no' : 'yes';
            this.$set(this.singleTemplate, 'is_enable', newValue);
        },

        setSubMenu(value) {
            this.subMenu = value;
        },

        arrayToString(arr, separator) {
            return arr.join(separator);
        }
    }
};
</script>
