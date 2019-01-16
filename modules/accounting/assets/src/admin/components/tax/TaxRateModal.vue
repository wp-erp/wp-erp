<template>
    <div class="wperp-modal wperp-modal-open wperp-printable-modal" role="dialog">
        <div class="wperp-modal-dialog" v-click-outside="outside" @click="inside">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <h1>
                        Journal
                    </h1>
                    <span class="modal-close"><i class="flaticon-close"></i></span>
                    <div class="d-print-none buttons-wrapper">
                        <a href="#" class="wperp-btn btn--default print-btn wperp-hidden-sm">
                            <i class="flaticon-printer-1"></i>
                            &nbsp; Print
                        </a>
                        <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                        <div class="wperp-has-dropdown">
                            <a href="#" class="wperp-btn btn--default dropdown-trigger m0">
                                <i class="flaticon-settings-work-tool"></i>
                                &nbsp; More Action
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#">Option1</a></li>
                                <li><a href="#">Option2</a></li>
                                <li><a href="#">Option3</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="wperp-modal-body pb-30">
                    <div class="wperp-invoice-panel">

                        <div class="wperp-invoice-table ">
                            <table class="wperp-table wperp-form-table invoice-table">
                                <thead>
                                <tr>
                                    <td class="col--check">SL.</td>
                                    <th class="column-primary">Account Name</th>
                                    <th class="column-primary">Descriptions</th>
                                    <th>Debit</th>
                                    <th class="text-right">Credit</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th class="col--check">{{ line_items.trn_no }}</th>
                                    <td class="column-primary">{{ line_items.ledger_id }}<button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button></td>
                                    <td data-colname="Debit">{{ line_items.particulars }}</td>
                                    <td data-colname="Debit">{{ line_items.debit }}</td>
                                    <td data-colname="Credit">{{ line_items.credit }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="hide-sm">
                                    <td colspan="3" class="text-right"><strong>Total = </strong></td>
                                    <td data-colname="Debit">{{total}}</td>
                                    <td data-colname="Credit">{{total}}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- particulars -->
                        <div class="particulars pl-30 pr-30">
                            <h4 class="mb-5 mt-10">Particulars</h4>
                            <span>{{particulars}}</span>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'

    export default {
        name: "TaxRateModal",

        data() {
            return {
                entry_id: 0,
                trn_date: '',
                particulars: '',
                line_items: {
                    trn_no: '',
                    ledger_id: '',
                    debit: 0,
                    credit: 0,
                },
                total: 0,
                acct_var: erp_acct_var
            }
        },

        created() {
            this.entry_id = this.$route.params.id;

            HTTP.get(`/journals/${this.entry_id}`).then((response) => {
                this.trn_date = response.data.trn_date;
                this.particulars = response.data.particulars;
                this.line_items = response.data.line_items;
                this.total = response.data.total;
            });
        },

        methods: {
            inside() {},

            outside() {
                this.$router.go(-1);
            }
        },
    }
</script>

<style scoped>

</style>
