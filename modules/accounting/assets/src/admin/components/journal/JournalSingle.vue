<template>
    <div class="wperp-modal-dialog journal-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h2>{{ __('Journal', 'erp') }}</h2>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; {{ __('Print', 'erp') }}
                    </a>
                </div>
            </div>
            <div class="wperp-modal-body">
                <div class="wperp-invoice-panel">
                    <div class="invoice-header" v-if="null != company">
                        <div class="invoice-logo">
                            <img :src="company.logo" alt="logo name" width="100" height="100">
                        </div>
                        <div class="invoice-address">
                            <address>
                                <strong>{{ company.name }}</strong><br>
                                {{ company.address.address_1 }}<br>
                                {{ company.address.address_2 }}<br>
                                {{ company.address.city }}<br>
                                {{ company.address.country }}
                            </address>
                        </div>
                    </div>

                    <div class="invoice-body">
                        <h4>{{ __('Journal', 'erp') }}</h4>
                        <div class="wperp-row" v-if="null != journal">
                            <div class="wperp-col-sm-12 pull-right">
                                <table class="invoice-info">
                                    <tr>
                                        <th>{{ __('Journal No', 'erp') }}:</th>
                                        <td>#{{ journal.id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Journal Ref', 'erp') }}:</th>
                                        <td>{{ journal.ref }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Journal Date', 'erp') }}:</th>
                                        <td>{{ formatDate(journal.trn_date) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != journal">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                            <tr class="inline-edit-row">
                                <th>{{ __('Account', 'erp') }}</th>
                                <th>{{ __('Particulars', 'erp') }}</th>
                                <th>{{ __('Debit', 'erp') }}</th>
                                <th>{{ __('Credit', 'erp') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :key="index" v-for="(line, index) in journal.line_items" class="inline-edit-row">
                                <td>{{ line.account}}</td>
                                <td>{{ line.particulars }}</td>
                                <td>{{ line.debit }}</td>
                                <td>{{ line.credit }}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr class="inline-edit-row">
                                <td colspan="7">
                                    <ul>
                                        <li><span>{{ __('Balance', 'erp') }}:</span> {{ moneyFormat(journal.total) }}</li>
                                    </ul>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

                <trans-particulars :particulars="journal.particulars" />

                <div class="invoice-attachments d-print-none">
                    <h4>{{ __('Attachments', 'erp') }}</h4>
                    <a class="attachment-item" :href="attachment"
                       :key="index"
                       v-for="(attachment, index) in journal.attachments" download>
                        <img :src="acct_var.acct_assets + '/images/file-thumb.png'">
                        <div class="attachment-meta">
                            <span>{{attachment.substring(attachment.lastIndexOf('/')+1) }}</span><br>
                            <!-- <span class="text-muted">file size</span> -->
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import TransParticulars from 'admin/components/transactions/TransParticulars.vue';

export default {
    name: 'JournalSingle',

    components: {
        TransParticulars
    },

    data() {
        return {
            company  : null,
            journal  : {},
            isWorking: false,
            acct_var : erp_acct_var /* global erp_acct_var */
        };
    },

    created() {
        this.getCompanyInfo();
        this.getJournal();
    },

    methods: {
        getCompanyInfo() {
            HTTP.get(`/company`).then(response => {
                this.company = response.data;
            }).then(e => {}).then(() => {
                this.isWorking = false;
            });
        },

        getJournal() {
            this.isWorking = true;
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get(`/journals/${this.$route.params.id}`).then(response => {
                this.journal = response.data;
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(e => {}).then(() => {
                this.isWorking = false;
            });
        },

        printPopup() {
            window.print();
        }
    }

};
</script>

<style lang="less">
    .journal-single {
        max-width: 960px;
        margin: 0 auto;
        .wperp-modal-footer {
            border-top: 1px solid #e2e2e2;
        }
        .wperp-modal-header {
            border-bottom: 1px solid #e2e2e2;
        }
        .wperp-form-field, input:not(.wperp-btn) {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }
    }

    @media print {
        .erp-nav-container {
            display: none;
        }
    }
</style>
