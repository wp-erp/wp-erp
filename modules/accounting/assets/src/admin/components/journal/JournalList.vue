<template>
    <div class="journals">
        <journal-modal :entry_id="journal_id" v-if="journalModal"/>

        <h2 class="add-new-journal">
            <span>{{ __('Journals', 'erp') }}</span>
            <a href="#" class="erp-journal-new" @click.prevent="$router.push({ name: 'JournalCreate' })">
                {{ __('New Journal Entry', 'erp') }}
            </a>
        </h2>

        <list-table
            tableClass="wp-ListTable widefat fixed journal-list"
            action-column="actions"
            :columns="columns"
            :rows="row_data"
            :total-items="paginationData.totalItems"
            :total-pages="paginationData.totalPages"
            :per-page="paginationData.perPage"
            :current-page="paginationData.currentPage"
            :showCb="false"
            @pagination="goToPage">
            <template slot="l_id" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'JournalSingle', params: { id: data.row.l_id }}">
                        #{{ data.row.l_id }}
                    </router-link>
                </strong>
            </template>
        </list-table>

    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';

export default {
    name: 'JournalList',

    components: {
        ListTable
    },

    data() {
        return {
            journalModal: false,
            columns: {
                l_id         : { label: 'Voucher No.' },
                l_date       : { label: 'Date' },
                l_particulars: { label: 'Particulars' },
                amount       : { label: 'Amount' }
            },
            rows: [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            journal_id: 0
        };
    },
    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.fetchItems();
    },

    computed: {
        row_data() {
            const items = this.rows;
            items.map(item => {
                item.l_id          = item.voucher_no;
                item.l_date        = item.trn_date;
                item.l_particulars = item.particulars;
                item.amount        = item.total;
            });
            return items;
        }
    },

    methods: {
        fetchItems() {
            this.rows = [];
            HTTP.get('/journals', {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page
                }
            }).then((response) => {
                this.rows = response.data;
                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch((error) => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        goToPage(page) {
            const queries = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name: 'PaginateJournals',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        }
    }

};
</script>
<style lang="less">
    .journals {
        .add-new-journal {
            margin-top:15px;
            align-items: center;
            display: flex;
            span {
                font-size: 18px;
                font-weight: bold;
            }
            a {
                background: #1a9ed4;
                border-radius: 3px;
                color: #fff;
                font-size: 12px;
                height: 29px;
                line-height: 29px;
                margin-left: 13px;
                text-align: center;
                text-decoration: none;
                width: 135px;
            }
        }
        .journal-list {
            border-radius: 3px;
            tbody {
                background: #FAFAFA;
            }
            th ul,
            th li {
                margin: 0;
            }
            th li {
                display: flex;
                align-items: center;
                img {
                    width: 22px;
                    padding-right: 5px;
                }
            }
            .column.title {
                &.selected {
                    color: #1A9ED4;
                }
                a {
                    color: #222;
                    font-weight: normal;
                    &:hover {
                        color: #1A9ED4;
                    }
                }
            }
            .check-column input {
                border-color: #E7E7E7;
                box-shadow: none;
                border-radius: 3px;
                &:checked {
                    background: #1ABC9C;
                    border-color: #1ABC9C;
                    border-radius: 3px;
                    &:before {
                        color: #fff;
                    }
                }
            }
            .row-actions {
                padding-left: 20px;
            }
        }
        .widefat {
            tfoot td,
            tbody th {
                line-height: 2.5em;
            }
            tbody td {
                line-height: 3em;
            }
        }
    }
</style>
