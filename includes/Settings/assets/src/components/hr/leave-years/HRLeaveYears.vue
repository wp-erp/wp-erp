
<template>
    <base-layout section_id="erp-hr" sub_section_id="financial">
        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRLeaveYearsForm">
            <div class="wperp-row">
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("Name", "erp") }}</label>
                </div>

                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("Start Date", "erp") }}</label>
                </div>

                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("End Date", "erp") }}</label>
                </div>
            </div>

            <div class="wperp-row" v-for="(year, index) in years_data" :key="index">
                <div class=" wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <input v-model="year.fy_name" class="wperp-form-field" />
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
                    + {{  __('Add New', 'erp') }}
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
    name: "HRLeaveYears",

    data() {
        return {
            years_data: [
                {
                    fy_name    : "",
                    description: "Year for leave",
                    start_date : "",
                    end_date   : "",
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
        submitHRLeaveYearsForm() {
            const self = this;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    fyears  : self.years_data,
                    _wpnonce: erp_settings_var.nonce,
                    action  : "erp-settings-financial-years-save",
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
                fy_name    : "",
                description: "Year for leave",
                start_date : "",
                end_date   : "",
                id         : null,
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
                    action  : "erp-settings-get-hr-financial-years",
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
                            self.years_data = [];
                            response.data.forEach((item) => {
                                item.start_date = self.formatDateFromTimestamp( item.start_date );
                                item.end_date   = self.formatDateFromTimestamp( item.end_date );
                                self.years_data.push(item);
                            });
                        }
                    }
                },
            });
        },

        formatDateFromTimestamp(timestamp) {
            if ( timestamp === null || timestamp === "" ) {
                return "";
            }

            return new Date(timestamp * 1e3).toISOString().slice(0, 10);
        },
    },
};
</script>
