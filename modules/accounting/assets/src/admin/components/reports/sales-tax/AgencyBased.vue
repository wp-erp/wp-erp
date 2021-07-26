<template>
    <div class="sales-tax-report">
        <h2 class="title-container">
            <span>{{ __( 'Sales Tax Report (Agency Based)', 'erp' ) }}</span>
            
            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'SalesTaxReportOverview' }">
                {{ __( 'Back', 'erp' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
                <div class="with-multiselect">
                    <multi-select v-model="selectedAgency" :options="taxAgencies"/>
                </div>

                <datepicker v-model="startDate" />
                
                <datepicker v-model="endDate" />
                
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">
                    {{ __( 'Filter', 'erp' ) }}
                </button>
            </div>


            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; {{ __( 'Print', 'erp' ) }}
            </a>
        </form>

        <ul class="report-header" v-if="null !== selectedAgency">
            <li>
                <strong>{{ __( 'Agency Name', 'erp' ) }}:</strong>
                <em> {{ selectedAgency.name }}</em>
            </li>

            <li>
                <strong>{{ __( 'Currency', 'erp' ) }}:</strong>
                <em> {{ symbol }}</em>
            </li>
            
            <li v-if="startDate && endDate">
                <strong>{{ __( 'For the period of (Transaction date)', 'erp' ) }}:</strong>
                <em> {{ formatDate( startDate ) }}</em> to <em>{{ formatDate( endDate ) }}</em>
            </li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="rows"
            :showCb="false">
            
            <template slot="trn_no" slot-scope="data">
                <strong>
                    <router-link
                        :to="{
                            name   : 'DynamicTrnLoader',
                            params : {
                                id : data.row.trn_no
                            }
                        }">
                        <span v-if="data.row.trn_no">#{{ data.row.trn_no }}</span>
                    </router-link>
                </strong>
            </template>
            
            <template slot="debit" slot-scope="data">
                {{ moneyFormat( data.row.debit ) }}
            </template>
            
            <template slot="credit" slot-scope="data">
                {{ moneyFormat( data.row.credit ) }}
            </template>
            
            <template slot="balance" slot-scope="data">
                {{ moneyFormat( data.row.balance ) }}
            </template>
            
            <template slot="tfoot">
                <tr class="tfoot">
                    <td colspan="3"></td>
                    <td>{{ __( 'Total', 'erp' ) }} =</td>
                    <td>{{ moneyFormat( totalDebit ) }}</td>
                    <td>{{ moneyFormat( totalCredit ) }}</td>
                    <td></td>
                </tr>
            </template>
        </list-table>
    </div>
</template>