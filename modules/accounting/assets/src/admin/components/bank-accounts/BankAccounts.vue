<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ pageTitle }}</h2>
                </div>
            </div>
        </div>

        <div class="wperp-transactions-section wperp-section">
            <!-- wperp-accounts-table class is required class for only this component -->
            <div class="table-container">
                <table class="wperp-table table-striped table-dark widefat table2 wperp-accounts-table">
                    <tbody>
                        <!-- keep this empty row if possible -->
                        <tr></tr>
                        <!--<tr>-->
                            <!--<td class="col&#45;&#45;account-infos">-->
                                <!--&lt;!&ndash; account name &ndash;&gt;-->
                                <!--<div class="account-name">-->
                                    <!--<h4>Checking Account</h4>-->
                                <!--</div>-->
                                <!--&lt;!&ndash; account number &ndash;&gt;-->
                                <!--<div class="account-number-info">-->
                                    <!--<span class="account-number-label">Account Number:</span>-->
                                    <!--<span class="account-number">258446798123435</span>-->
                                <!--</div>-->
                                <!--&lt;!&ndash; account balance info &ndash;&gt;-->
                                <!--<div class="account-balance-info">-->
                                    <!--&lt;!&ndash; available balance &ndash;&gt;-->
                                    <!--<div class="available-balance">-->
                                        <!--<span class="account-balance-label">Available Balance:</span>-->
                                        <!--<strong class="account-balance">$258446798123435</strong>-->
                                    <!--</div>-->
                                    <!--&lt;!&ndash; current balance &ndash;&gt;-->
                                    <!--<div class="current-balance">-->
                                        <!--<span class="account-balance-label">Current Balance:</span>-->
                                        <!--<strong class="account-balance">$258446798123435</strong>-->
                                    <!--</div>-->
                                <!--</div>-->
                            <!--</td>-->
                            <!--&lt;!&ndash; actions column &ndash;&gt;-->
                            <!--<td class="col&#45;&#45;actions">-->
                                <!--<div class="wperp-has-dropdown dropdown right&#45;&#45;middle">-->
                                    <!--<a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>-->
                                    <!--<ul class="dropdown-menu" role="menu">-->
                                        <!--<li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>-->
                                        <!--<li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>-->
                                    <!--</ul>-->
                                <!--</div>-->
                            <!--</td>-->
                        <!--</tr>-->
                        <tr v-for="account in accounts">
                            <td class="col--account-infos">
                                <!-- account name -->
                                <div class="account-name">
                                    <h4>{{account.name}}</h4>
                                </div>
                                <!-- account number -->
                                <div class="account-number-info">
                                    <span class="account-number-label">Account Number:</span>
                                    <span class="account-number">{{account.id}}</span>
                                </div>
                                <!-- account balance info -->
                                <div class="account-balance-info">
                                    <!-- available balance -->
                                    <div class="available-balance">
                                        <span class="account-balance-label">Available Balance:</span>
                                        <strong class="account-balance">{{transformBalance(account.balance)}}</strong>
                                    </div>

                                    <!-- current balance -->
                                    <!--<div class="current-balance">-->
                                        <!--<span class="account-balance-label">Current Balance:</span>-->
                                        <!--<strong class="account-balance">$258446798123435</strong>-->
                                    <!--</div>-->
                                </div>
                            </td>
                            <!-- actions column -->
                            <td class="col--actions">
                                <div class="wperp-has-dropdown dropdown right--middle">
                                    <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                        <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</template>

<script>
    import HTTP from 'admin/http';
    export default {
        name: 'bankAccounts',

        components: {
            HTTP
        },

        data () {
            return {
                pageTitle: 'Accounts',
                accounts: []
            }
        },

        created (){
            this.fetchAccounts();
        },

        methods: {
            fetchAccounts(){
                HTTP.get('transfer-voucher').then( (response) => {
                    this.accounts = response.data;
                } );
            },

            transformBalance( val ){
                let currency = '$';
                if ( val < 0 ){
                    return `Cr. ${currency} ${Math.abs(val)}`;
                }

                return `Dr. ${currency} ${val}`;
            },
        }

    };
</script>

<style lang="less" scoped>
    .wperp-accounts-table {
        border: 0;
        .col--actions {
            vertical-align: top;
            line-height: 20px;
            padding-right: 17px;
        }
        td {
            padding-top: 20px;
            padding-bottom: 20px;
        }
    }
    .account-name h4 {
        font-weight: 500;
        margin-bottom: 13px;
        color: #000;
    }
    .account-balance {
        font-weight: 500;
        color: #1A9ED4;
        font-size: 14px;
    }
    .col--account-infos {
        > div {
            line-height: 18px;
            span {
                color: #525252;
                &.account-number {
                    font-weight: 400;
                    color: 000;
                }
            }
        }
    }
    .account-number-label, .account-balance-label {
        display: inline-flex;
        min-width: 120px;
    }
    .account-balance-info {
        margin-top: 9px;
        display: flex;
        > div {
            &:not(:last-child) {
                padding-right: 20px;
                margin-right: 20px;
                border-right: 1px solid #D8D8D8;
            }
        }
    }
    @media (max-width: 782px){
        .wperp-table tbody tr:not(.inline-edit-row):not(.no-items) td {
            padding-left: 10px;
        }
    }
</style>
