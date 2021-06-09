<template>
    <hr-payroll>
        <form action="" class="wperp-form" method="post" @submit.prevent="onFormSubmit(false)">
            <h3 class="sub-sub-title">{{ subSubSectionTitle }}</h3>

            <div class="wperp-form-group" v-if="typeof options[1] !== 'undefined'">
                <label>{{ options[1].title }}</label>

                <select v-model="fields.paytype" class="wperp-form-field">
                    <option
                        v-for="(item, keyOption, indexOption) in options[1].options"
                        :value="keyOption"
                        :key="indexOption"
                    >
                        {{ item }}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group" v-if="typeof options[2] !== 'undefined'">
                <label>{{ options[2].title }}</label>
                <input v-model="fields[options[2]['id']]" class="wperp-form-field" />
            </div>

            <div class="wperp-form-group">
                <submit-button :text="__('Add Pay Item', 'erp')" />
            </div>
        </form>

        <table class="erp-settings-table widefat">
            <thead>
                <tr>
                    <th>{{ __('Pay Type', 'erp') }}</th>
                    <th>{{ __('Pay Item', 'erp') }}</th>
                    <th>{{ __('Amount Type', 'erp') }}</th>
                    <th>{{ __('Action', 'erp') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr valign="top" v-for="(payitem, index) in payItemList" :key="index">
                    <td>{{ payitem.type }}</td>
                    <td>{{ payitem.payitem }}</td>
                    <td>{{ payitem.pay_item_add_or_deduct == '1' ? 'Addition' : 'Subtraction' }}</td>
                    <td>
                        <span @click="popupPayItem(payitem, true)" class="action"><i class="fa fa-pencil"></i></span>
                        <span @click="deletePayItem(payitem.id)" class="action"><i class="fa fa-trash"></i></span>
                    </td>
                </tr>
            </tbody>
        </table>

        <modal v-show="modal.isVisible" :title="__( 'Edit Pay Item', 'erp' )" @close="popupPayItem(null, false)" :header="true" :footer="true" :hasForm="true">
            <template v-slot:body>
                <form class="wperp-form" method="post" @submit.prevent="onFormSubmit(true)">
                    <div class="wperp-form-group" v-if="typeof options[2] !== 'undefined'">
                        <label>{{ options[2].title }}</label>
                        <input
                            v-model="editedItem[options[2].id]"
                            class="wperp-form-field"
                        />
                    </div>

                    <div class="wperp-form-group">
                        <submit-button :text="__('Update', 'erp')" />
                    </div>
                </form>

            </template>
        </modal>

    </hr-payroll>
</template>

<script>
import Modal from "settings/components/base/Modal.vue";
import SubmitButton from "settings/components/base/SubmitButton.vue";
import HrPayroll from "settings/components/hr/payroll/HrPayroll.vue";
import BaseContentLayout from "settings/components/layouts/BaseContentLayout.vue";
import { generateFormDataFromObject } from "settings/utils/FormDataHandler";
var $ = jQuery;

export default {
    name: "HrPayItem",

    data() {
        return {
            section_id        : "erp-hr",
            sub_section_id    : "payroll",
            subSubSectionTitle: "",
            options           : [],
            fields            : {},
            payItemList       : [],
            editedItem        : {
                'payitem' : ''
            },
            modal             : {
                isVisible: false
            },
        };
    },

    components: {
        HrPayroll,
        BaseContentLayout,
        SubmitButton,
        Modal
    },

    created() {
        const menus             = erp_settings_var.erp_settings_menus;
        const parentMenu        = menus.find(menu => menu.id === this.section_id);

        this.options            = parentMenu.fields[ this.sub_section_id ]['payitem'];
        this.subSubSectionTitle = this.options.length > 0 ? this.options[0].title : '';

        this.$store.dispatch("spinner/setSpinner", true);
        this.getPaymentData();
    },

    methods: {
        getPaymentData() {
            const self = this;

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    ...self.options,
                    _wpnonce      : typeof this.options[3] !== 'undefined' ? this.options[3].nonce : '',
                    action        : "erp_payroll_get_payitem",
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
                        self.payItemList = response.data;
                    }
                },
            });
        },

        popupPayItem( editedPayItem, visibility = true ) {
            this.modal.isVisible = visibility;

            if ( visibility ) {
                this.editedItem = editedPayItem
            }
        },

        deletePayItem( id ) {
            const self = this;

            if ( confirm( __('Are you sure to delete the pay item ?', 'erp') ) ) {
                const postedData = {
                    id: id
                }

                let requestData = {
                    ...postedData,
                    action  : 'erp_payroll_remove_payitem',
                    _wpnonce: typeof this.options[3] !== 'undefined' ? this.options[3].nonce : '',
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
                            self.showAlert("success", response.data);
                            self.getPaymentData();
                        } else {
                            self.showAlert("error", response.data);
                        }
                    },
                });
            }
        },

        onFormSubmit( isUpdate = false ) {
            const self = this;
            self.$store.dispatch("spinner/setSpinner", true);

            const postedData = ! isUpdate ? self.fields : self.editedItem

            let requestData = {
                ...postedData,
                action  : ! isUpdate ? 'erp_payroll_add_payitem' : 'erp_payroll_edit_payitem',
                _wpnonce: typeof this.options[3] !== 'undefined' ? this.options[3].nonce : '',
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
                            self.editedItem = null;
                            self.modal.isVisible = false;
                        }

                        self.fields = {};
                        self.showAlert("success", response.data);
                        self.getPaymentData();
                    } else {
                        self.showAlert("error", response.data);
                    }
                },
            });

        },
    },
};
</script>
