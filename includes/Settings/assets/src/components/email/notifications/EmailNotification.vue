<template>
    <base-layout
        :section_id="section"
        :sub_section_id="subSection"
        :enable_content="false"
    >
        <div id="erp-email-templates">
            <ul class="sub-sub-menu">
                <li v-for="(menu, key, index) in options.sub_sections" :key="key">
                    <a :class="key === module? 'router-link-active': ''" @click="setModule(key)">
                        <span class="menu-name">{{ menu }}</span>
                    </a>
                </li>
            </ul>

            <table class="erp-settings-table widefat email-template-table">
                <thead>
                    <tr>
                        <th v-for="(column, index) in columns" :key="index" :class="column.class">{{ column.name }}</th>
                    </tr>
                </thead>

                <tbody v-if="Object.keys(emails).length">
                    <tr valign="top" v-for="(email, index) in emails" :key="index">
                        <td>
                            <span
                                class="template-name"
                                @click="configureTemplate(email)">
                                {{ email.name }}
                            </span>
                        </td>
                        <td class="hide-sm">{{ email.description }}</td>
                        <td>
                            <radio-switch
                                v-if="email.disable_allowed"
                                :value="email.is_enabled"
                                :id="email.id"
                                @toggle="toggleStatus(email, index)"
                            ></radio-switch>
                        </td>
                        <td>
                            <button
                                class="wperp-btn btn--primary button"
                                @click="configureTemplate(email)"
                                :id="email.option_id">
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
        </div>

        <modal
            v-if="showModal"
            :title="__('Configure Template', 'erp')"
            @close="toggleModal"
            :header="true"
            :footer="true"
            :hasForm="true">
            <template v-slot:body v-if="singleTemplate">
                <h4>{{ singleTemplate.title }}</h4>
                <p>{{ singleTemplate.description }}</p>
                <form class="wperp-form" method="post" @submit.prevent="onSubmit">
                    <div class="wperp-form-group" v-if="singleTemplate.disable_allowed">
                        <label>{{ __('Disable/Enable', 'erp') }}</label>
                        <radio-switch
                            v-model="singleTemplate.is_enable"
                            :id="singleTemplate.id"
                            @toggle="switchValue"
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
                        <vue-trix v-model="singleTemplate.body" placeholder="Enter content"/>
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

                        <div class="email-template-tags"><span v-for="(tag, key) in shortCodes" :key="key">{{ tag }}</span></div>
                    </div>
                </form>
            </template>

            <div v-else class="regen-sync-loader"></div>

            <template v-slot:footer>
                <span @click="onSubmit">
                    <submit-button :text="__('Save', 'erp')" customClass="pull-right"/>
                </span>

                <span @click="toggleModal">
                    <submit-button :text="__('Cancel', 'erp')" customClass="wperp-btn-cancel pull-right" style="margin-right: 7px;"/>
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
            emailTemplates  : {},
            singleTemplate  : [],
            shortCodes      : [],
            module          : 'hrm',
            showModal       : false,
            columns         : [
                {
                    name: __('Template Name', 'erp'),
                    class: ''
                },
                {
                    name: __('Description', 'erp'),
                    class: 'hide-sm'
                },
                {
                    name: __('Disable / Enable', 'erp'),
                    class: ''
                }
            ],
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
            return Object.keys(this.emailTemplates).length
                && this.emailTemplates[ this.module ] !== undefined
                ? this.emailTemplates[ this.module ] : [];
        },

        numColumns() {
            return this.columns.length;
        }
    },

    methods: {
        getEmailTemplates() {
            var self = this;

            self.$store.dispatch("spinner/setSpinner", true);

            setTimeout(() => {
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
                        self.$store.dispatch("spinner/setSpinner", false);
                        self.showAlert("error", error);
                    }
                });
            }, 200);
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
                    self.$set(self.emailTemplates[self.module][index], 'is_enabled', status);
                    self.$store.dispatch("spinner/setSpinner", false);
                },
                error: function(error) {
                    self.$store.dispatch("spinner/setSpinner", false);
                    self.showAlert("error", error);
                }
            });
        },

        configureTemplate(template) {
            var self = this;

            self.$store.dispatch("spinner/setSpinner", true);

            wp.ajax.send({
                data : {
                    template : template.id,
                    action   : "erp_get_single_email_template",
                    _wpnonce : erp_settings_var.nonce,
                },
                success: function(response) {
                    self.singleTemplate             = response;
                    self.singleTemplate.title       = template.name;
                    self.singleTemplate.description = template.description;
                    self.shortCodes                 = response.tags;
                    self.showModal                  = true;

                    self.$store.dispatch("spinner/setSpinner", false);
                },
                error: function(error) {
                    self.$store.dispatch("spinner/setSpinner", false);
                    self.showAlert("error", error);
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

            requestData = window.settings.hooks.applyFilters( "requestData", requestData );

            wp.ajax.send({
                data    : requestData,
                success : function(response) {
                    self.$store.dispatch("spinner/setSpinner", false);
                    self.showModal = false;
                    self.showAlert("success", response);
                },
                error   : function(error) {
                    self.$store.dispatch("spinner/setSpinner", false);
                    self.showAlert("error", error);
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

        setModule(value) {
            this.$store.dispatch("spinner/setSpinner", true);

            setTimeout(() => {
                this.module = value;
                this.$store.dispatch("spinner/setSpinner", false);
            }, 150);
        },
    }
};
</script>

<style scoped>
    .sub-sub-menu {
        margin-top: 0;
        margin-bottom: 30px;
    }
</style>
