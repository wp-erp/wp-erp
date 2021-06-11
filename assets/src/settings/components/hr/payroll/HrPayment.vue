<template>
    <hr-payroll>
        <form action="" class="wperp-form" method="post" @submit.prevent="onFormSubmit">
            <h3 class="sub-sub-title">
                {{ subSubSectionTitle }}
            </h3>

            <div class="wperp-form-group" v-if="typeof options[0] !== 'undefined'">
                <label>{{ options[0].title }}</label>

                <select
                    v-model="options[0]['value']"
                    class="wperp-form-field"
                    v-if="options[0].type === 'select'"
                    :id="options[0]['id']"
                >
                    <option
                        v-for="(item, keyOption, indexOption) in options[0].options"
                        :value="keyOption"
                        :key="indexOption"
                    >
                        {{ item }}
                    </option>
                </select>
            </div>

            <template v-if="typeof options[0] !== 'undefined' && typeof options[1] !== 'undefined'">
                <div class="wperp-form-group" v-if="options[0]['value'] !== 'cash'">
                    <label>{{ options[1].title }}</label>

                    <select
                        v-model="options[1]['value']"
                        class="wperp-form-field erp-select2"
                        v-if="options[1].type === 'select'"
                        :id="options[1]['id']"
                    >
                        <option
                            v-for="(item, keyOption, indexOption) in options[1].options"
                            :value="keyOption"
                            :key="indexOption"
                        >
                            {{ item }}
                        </option>
                    </select>
                </div>
            </template>

            <div class="wperp-form-group">
                <submit-button :text="__('Save Changes', 'erp')" />
            </div>
        </form>
    </hr-payroll>
</template>

<script>
import SubmitButton from "settings/components/base/SubmitButton.vue";
import HrPayroll from "settings/components/hr/payroll/HrPayroll.vue";
import BaseContentLayout from "settings/components/layouts/BaseContentLayout.vue";
import { generateFormDataFromObject } from "settings/utils/FormDataHandler";

var $ = jQuery;

export default {
    name: "HrPayment",

    data() {
        return {
            section_id        : "erp-hr",
            sub_section_id    : "payroll",
            subSubSectionTitle: "",
            options           : []
        };
    },

    components: {
        HrPayroll,
        BaseContentLayout,
        SubmitButton
    },

    created() {
        const menus             = erp_settings_var.erp_settings_menus;
        const parentMenu        = menus.find(menu => menu.id === this.section_id);

        this.options            = parentMenu.fields[ this.sub_section_id ]['payment'];
        this.subSubSectionTitle = this.options.length > 0 ? this.options[0].title : '';
        let newOptions          = [];

        this.options.forEach(option => {
            if ( option.type !== 'title' && option.type !== 'sectionend' ) {
                newOptions.push( option );
            }
        });

        this.options = newOptions;

        this.getPaymentData();
    },

    methods: {
        getPaymentData() {
            const self = this;

            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    ...self.options,
                    _wpnonce      : erp_settings_var.nonce,
                    action        : "erp-settings-get-data",
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
                        self.options = response.data;
                    }
                },
            });
        },

        onFormSubmit() {
            const self          = this;
            let requestDataPost = {};

            self.$store.dispatch("spinner/setSpinner", true);

            self.options.forEach( item => {
                requestDataPost[item.id] = item.value;

                if ( item.value === false || item.value === "no" ) {
                    requestDataPost[item.id] = null;
                }
            } );

            let requestData = {
                ...requestDataPost,
                _wpnonce       : erp_settings_var.nonce,
                action         : "erp-settings-save",
                module         : self.section_id,
                section        : self.sub_section_id,
                sub_sub_section: 'payment'
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
                        self.showAlert("success", response.data.message);
                    } else {
                        self.showAlert("error", response.data);
                    }
                },
            });
        }
    },
};
</script>
