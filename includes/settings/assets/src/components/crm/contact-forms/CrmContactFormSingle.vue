<template>
    <div>
        <h3 class="sub-section-title mb-20"> {{ formData.title }} </h3>

        <form class="wperp-form" method="post">
            <table class="erp-settings-table widefat" style="max-width: 600px">
                <thead>
                    <tr>
                        <th>{{ __('Form Field', 'erp') }}</th>
                        <th>{{ __('CRM Contact Option	', 'erp') }}</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody v-for="fieldKey in Object.keys(formData.fields)" :key="fieldKey">
                    <tr valign="top">
                        <td>{{ formData.fields[fieldKey] }}</td>
                        <td>{{ getCRMOptionTitle(fieldKey) }}</td>
                        <td>
                        <button
                                type="button"
                                class="button button-default"
                                v-on:click="resetMapping(fieldKey)"
                                :disabled="isMapped(fieldKey)"
                            >
                                <i class="dashicons dashicons-no-alt"></i>
                            </button>
                            <button type="button" class="button button-default" v-on:click="setActiveDropDown(fieldKey)">
                                <i class="dashicons dashicons-screenoptions"></i>
                            </button>
                        </td>
                    </tr>

                    <tr class="cfi-option-row" v-show="fieldKey === activeDropDown">
                        <td colspan="3" class="cfi-contact-options">

                            <span v-for="(optionTitle, option, index) in data.crmOptions" :key="index">
                                <button
                                    type="button"
                                    v-if="! optionIsAnObject(option)"
                                    v-on:click="mapOption(fieldKey, option)"
                                    :class="['button', isOptionMapped(fieldKey, option) ? 'button-primary active' : '']"
                                    style="margin: 3px"
                                >
                                {{ optionTitle }}
                                </button>
                            </span>


                            <span v-for="(option, options, index2) in data.crmOptions" :key="`parent-${index2}`">
                                <span v-for="(childOptionTitle, childOption, index3) in options.options" :key="`child-${index3}`">
                                    <button
                                        type="button"
                                        v-if="optionIsAnObject(option)"
                                        v-on:click="mapChildOption(field, option, childOption)"
                                        :class="['button', isChildOptionMapped(field, option, childOption) ? 'button-primary active' : '']"
                                        style="margin: 3px"
                                    >
                                        {{ options.title + ' - ' + childOptionTitle }}
                                    </button>
                                </span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="wperp-form-group mt-10">
                <label>{{ i18n.labelContactGroups }}</label>
                <select class="" v-model="formData.contactGroup">
                    <option value="0">{{ i18n.labelSelectGroup }}</option>
                    <option v-for="(groupTitle, groupId) in data.contactGroups" :value="groupId" :key="groupId">
                        {{ groupTitle }}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group mt-10">
                <label>{{ i18n.labelContactOwner }} <span class="required">*</span></label>
                <select class="" v-model="formData.contactOwner">
                    <option value="0">{{ i18n.labelSelectGroup }}</option>
                    <option v-for="(userName, userId) in data.contactOwners" :value="userId" :key="userId">
                        {{ userName }}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group mt-10">
                <button type="button" class="wperp-btn btn--default settings-button-reset pull-left" v-on:click="reset_mapping">
                    {{ __( 'Reset', 'erp' ) }}
                </button>

                <button type="button" class="wperp-btn btn--primary settings-button ml-10 mt-0" v-on:click="save_mapping">
                    {{ __( 'Save Changes', 'erp' ) }}
                </button>
                <div class="clearfix"></div>
            </div>
        </form>
    </div>
</template>

<script>

var $ = jQuery;

export default {
    name: "CrmContactFormSingle",

    data() {
        return {
            contactGroups : {},
            contactOwners : {},
            crmOptions    : {},
            i18n          : {},
            mappedData    : "",
            nonce         : "",
            activeDropDown: ""
        };
    },

    props: {
        plugin: {
            type    : String,
            required: true,
        },
        formId: {
            type    : Number,
            required: true,
        },
        formData: {
            type    : Object,
            required: true
        },
        data: {
            type    : Object,
            required: true
        }
    },

    created() {
        // Process the data and put other things in local variable
        if ( typeof this.data !== 'undefined' ) {
            this.contactGroups = this.data.contactGroups,
            this.contactOwners = this.data.contactOwners,
            this.crmOptions    = this.data.crmOptions,
            this.i18n          = this.data.i18n,
            this.mappedData    = this.data.mappedData,
            this.nonce         = this.data.nonce
        }
    },

    computed: {
        totalFields: function () {
            return Object.keys(this.formData.fields).length;
        }
    },

    methods: {

        lastOfTypeClass: function (index) {
            return index === (this.totalFields - 1) ? 'cfi-mapping-row-last' : '';
        },

        getCRMOptionTitle: function (field) {
            var option = this.formData.map[field],
                title = '';

            if (option && option.indexOf('.') < 0) {
                title = this.crmOptions[ option ];

            } else if (option) {
                var arr = option.split('.');
                title = this.crmOptions[arr[0]].title + ' - ' + this.crmOptions[arr[0]].options[arr[1]];
            }

            return title ? title : this.i18n.notMapped;
        },

        optionIsAnObject: function (option) {
            return '[object Object]' === Object.prototype.toString.call(this.crmOptions[option]);
        },

        mapOption: function (field, option) {
            this.formData.map[field] = option;
        },

        mapChildOption: function (field, option, childOption) {
            this.formData.map[field] = option + '.' + childOption;
        },

        isMapped: function (field) {
            return !this.formData.map[field];
        },

        isOptionMapped: function (field, option) {
            return this.formData.map[field] === option;
        },

        isChildOptionMapped: function (field, option, childOption) {
            return this.formData.map[field] === (option + '.' + childOption);
        },

        resetMapping: function (field) {
            this.formData.map[field] = null;
        },

        setActiveDropDown: function (field) {
            this.activeDropDown = (field === this.activeDropDown) ? null: field;
        },

        save_mapping: function (e) {
            e.preventDefault();
            this.makeAjaxRequest('erp_settings_save_contact_form');
        },

        reset_mapping: function (e) {
            e.preventDefault();
            this.makeAjaxRequest('erp_settings_reset_contact_form');
        },

        makeAjaxRequest: function (action) {
            var self = this;
            var postData = {
                action      : action,
                _wpnonce    : self.data.nonce,
                plugin      : self.plugin,
                formId      : self.formId,
                map         : self.formData.map,
                contactGroup: self.formData.contactGroup,
                contactOwner: self.formData.contactOwner,
            }

            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                dataType: 'json',
                data    : postData

            }).done(function (response) {

                if ('erp_settings_reset_contact_form' === action && response.success) {
                    const data = {
                        ...postData,
                        map         : response.map,
                        contactGroup: response.contactGroup,
                        contactOwner: response.contactOwner
                    }
                    self.$emit('reset_contact_form_data', data)
                }

                var type = response.success ? 'success' : 'error';

                if ( response.msg ) {
                    self.showAlert( type, response.msg );
                }
            });
        }

    },

    watch: {
        'formData.map': {
            deep: true,
            handler: function (newVal) {
                this.formData.map = newVal;
            }
        },

        'formData.contactGroup': function (newVal) {
            this.formData.contactGroup = newVal;
        }
    }
};
</script>
