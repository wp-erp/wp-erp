
<template>
    <base-layout section_id="erp-ac" sub_section_id="opening_balance">

        <form action="" class="wperp-form" method="post" @submit.prevent="submitAcFinancialYearsForm">
            <div class="wperp-row">
                <div class=" wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("Name", "erp") }}</label>
                </div>

                <div class=" wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("Start Date", "erp") }}</label>
                </div>

                <div class=" wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("End Date", "erp") }}</label>
                </div>
            </div>

            <div class="wperp-row" v-for="(year, index) in years_data" :key="index">
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <input v-model="year.name" class="wperp-form-field" />
                </div>

                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <date-picker class="wperp-form-field" :placeholder="__('Start date', 'erp')" v-model="year.start_date" />
                </div>

                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <date-picker class="wperp-form-field" :placeholder="__('End date', 'erp')" v-model="year.end_date" />
                    <span v-if="index > 0" class="settings-btn-cancel" @click="deleteYear(index)">x</span>
                </div>
            </div>

            <div class="wperp-form-group">
                <button class="wperp-btn wperp-btn-default" type="button" @click="addNewYear">
                    + Add New
                </button>
            </div>

            <div class="wperp-form-group">
                <submit-button :text="__('Save Changes', 'erp')" />
                <div class="clearfix"></div>
            </div>
        </form>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import DatePicker from "../../base/DatePicker.vue";
import SubmitButton from "../../base/SubmitButton.vue";
import { generateFormDataFromObject } from "../../../utils/FormDataHandler";

var $ = jQuery;

export default {
    name: "AcFinancialYears",

    data() {
        return {
            years_data: [
                {
                    name          : '',
                    start_date    : '',
                    end_date      : '',
                    description   : 'Accounting Financial Years'
                },
            ],
        };
    },

    components: {
        BaseLayout,
        DatePicker,
        SubmitButton
    },

    created() {
        this.$store.dispatch("spinner/setSpinner", true);
        this.getFinancialYearsData();
    },

    methods: {
        submitAcFinancialYearsForm() {
            const self = this;

            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    fyears  : self.years_data,
                    _wpnonce: erp_settings_var.nonce,
                    action  : "erp-settings-ac-financial-years-save",
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
                        self.getFinancialYearsData();
                        self.showAlert("success", response.data.message);
                    } else {
                        self.showAlert("error", response.data);
                    }
                },
            });
        },

        addNewYear() {
            this.years_data.push({
                name       : '',
                start_date : '',
                end_date   : '',
                description:  'Accounting Financial Years'
            });
        },

        deleteYear(index) {
            this.years_data.splice(index, 1);
        },

        getFinancialYearsData() {
            const self = this;

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    _wpnonce: erp_settings_var.nonce,
                    action: "erp-settings-get-ac-financial-years",
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
                        if (response.data.length > 0) {
                            self.years_data = response.data;
                        }
                    }
                },
            });
        }
    },
};
</script>
