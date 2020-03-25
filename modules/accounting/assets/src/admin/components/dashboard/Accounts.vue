<template>
    <div :class="['bank-accounts-section', 'wperp-panel', 'wperp-panel-default', ( isEditSettingsEnabled ? 'open-edit':'' )]">
        <div class="wperp-panel-heading wperp-bg-white">
            <h4>{{ __('Accounts', 'erp') }}</h4>
        </div>
        <div class="wperp-panel-body pb-0">
            <ul v-if="accounts.length" class="wperp-list-unstyled list-table-content list-table-content--border">
                <li :key="key" v-for="(data,key) in accounts">
                    <div class="left">
                        <i class="flaticon-menu-1"></i>
                        <details v-if="data.additional" open>
                            <summary>{{ data.name }}</summary>
                            <p :key="additional.id" v-for="additional in data.additional">
                                {{ additional.name }}
                                {{ moneyFormat(Math.abs(additional.balance)) }}
                            </p>
                        </details>
                        <span v-else>{{ data.name }}</span>
                    </div>
                    <div class="right">
                        <span v-if="undefined === data.balance" class="price">{{formatDBAmount(0)}}</span>
                        <span v-else class="price">{{formatDBAmount(data.balance)}}</span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="wperp-panel-footer mt-50">
            <div class="bank-accounts-total">
                <span class="title">{{ __('Total Balance', 'erp') }}</span>
                <span class="price">{{totalAmount}}</span>
            </div>
        </div>
    </div>

</template>

<script>
import HTTP from 'admin/http';

export default {
    name: 'Accounts',

    data() {
        return {
            isEditSettingsEnabled: false,
            accounts             : [],
            edit_accounts        : []
        };
    },

    computed: {
        totalAmount() {
            if ((typeof this.accounts === 'object' && this.accounts === null)) {
                return;
            }
            let amount = 0;

            if (this.accounts) {
                this.accounts.forEach(element => {
                    if (element.balance === null) {
                        element.balance = 0;
                    }
                    amount += parseFloat(element.balance);
                });
            }

            if (isNaN(parseFloat(amount))) {
                amount = 0;
            }

            return this.formatAmount(amount);
        }
    },

    created() {
        this.fetchAccounts();
    },

    methods: {
        fetchAccounts() {
            HTTP.get('/accounts/cash-at-bank').then(response => {
                this.accounts = response.data;
            });
        }
    }
};
</script>

<style scoped>
</style>
