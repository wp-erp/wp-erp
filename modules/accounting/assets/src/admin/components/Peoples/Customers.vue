<template>
    <div class="app-customers">
        <h2 class="add-new-customer">
            <span>Customers</span>
            <a href="#" id="erp-customer-new" @click="showModal = true">+ Add New Customer</a>
        </h2>
        <ListTable
            tableClass="wp-ListTable widefat fixed customer-list"
            action-column="actions"
            :columns="columns"
            :rows="rows"
            :bulk-actions="bulkActions"
            :total-items="4"
            :total-pages="2"
            :per-page="2"
            :current-page="1"
            :actions="[
                { key: 'edit', label: 'Edit' },
                { key: 'trash', label: 'Delete' }
            ]">
            <template slot="title" slot-scope="data">
                <strong><a href="#">{{ data.row.title }}</a></strong>
            </template>
        </ListTable>

    </div>
</template>

<script>
    import ListTable from '../ListTable/ListTable.vue'

    export default {
        name: 'Customers',
        components: {
            ListTable
        },
        data () {
            return {
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                    }
                ],
                columns: {
                    'customer': { label: 'Customer Name' },
                    'company': { label: 'Company' },
                    'email': { label: 'Email' },
                    'phone': { label: 'Phone' },
                    'expense': { label: 'Expense' },
                    'actions': { label: 'Actions' }
                },
                rows: [
                    {
                        id: 1,
                        customer: 'John Smith',
                        company: 'Com 1',
                        email: 'asd@gmail.com',
                        phone: '+32834239',
                        expense: '20000'
                    },
                    {
                        id: 2,
                        customer: 'John Doe',
                        company: 'Com 2',
                        email: 'fgh@gmail.com',
                        phone: '+235235234',
                        expense: '324234'
                    }
                ]
            };
        },
        created() {
            this.$on('modal-close', function() {
                this.showModal = false;
            });
        }
    };
</script>
<style lang="less">
    .app-customers {
        .add-new-customer {
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
        .customer-list {
            border-radius: 3px;
            tbody {
                background: #FAFAFA;
            }
            tfoot th,
            thead th {
                color: #1A9ED4;
                font-weight: bold;
            }
            th ul,
            th li {
                margin: 0;
            }
            th li {
                display: flex;
                align-items: center;
                img {
                    width: 14px;
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
