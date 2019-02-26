<template>
<div class="wperp-container">

        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Create New Account</h2>
                </div>
            </div>
        </div>

        <form action="" class="chart-accounts" @submit.prevent="saveAccount">

            <div class="form-row" v-if="error">
                <p class="error-message">{{ error }}</p>
            </div>

            <div class="form-row">
                <label for="">Select chart of accounts</label>
                 <treeselect v-model="ledgFields.chart_id"
                    :options="chartAccounts"
                    :disable-branch-nodes="true"
                    :show-count="true"
                    placeholder="Please select" />
            </div>

            <div class="form-row">
                <label for="">Select Category (optional)</label>
                <treeselect v-model="ledgFields.category_id"
                    :options="categories"
                    :disable-branch-nodes="true"
                    :show-count="true"
                    placeholder="Please select a category">

                    <label slot="option-label" slot-scope="{ node, shouldShowCount, count, labelClassName, countClassName }" :class="labelClassName">
                        {{ node.label }}
                        <span v-if="shouldShowCount" :class="countClassName">({{ count }})</span>
                        <span class="list-actions" v-if="node.raw.system == null">
                            <strong class="edit" @click.prevent="editCategory(node)">&#9998;</strong>
                            <strong class="remove" @click.prevent="removeCategory(node)">&cross;</strong>
                        </span>
                    </label>
                </treeselect>

                <a href="#" @click.prevent="categoryAddModal" role="button" class="after-select-dropdown">Add new category</a>
            </div>

            <div class="form-row">
                <label for="">Account Name</label>

                <input type="text" v-model="ledgFields.name" required>
            </div>

            <div class="form-row">
                <label for="">Code (optional)</label>

                <input type="number" v-model="ledgFields.code">
            </div>

            <button class="wperp-btn btn--primary" type="submit">
                {{ isChartAdding ? 'Saving...': 'Save' }}
            </button>
        </form>

        <cat-add-modal v-if="catAddModal" :categories="categories" :catData="catData" />

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Treeselect from '@riophae/vue-treeselect'
    import '@riophae/vue-treeselect/dist/vue-treeselect.css'
    import CatAddModal from 'admin/components/chart-accounts/CatAddModal.vue';

    export default {
        name: 'AddChartAccounts',

        data() {
            return {
                chartAccounts: [],
                categories: [],
                catAddModal: false,

                ledgFields: {
                    chart_id: null,
                    category_id: null,
                    account_name: '',
                    code: ''
                },

                catData: {
                    title: 'Add New',
                    node: null
                },

                error: false,
                isChartAdding: false,
            };
        },

        components: {
            Treeselect,
            CatAddModal,
        },

        created() {
            this.fetchChartAccounts();
            this.fetchLedgerCategories();

            this.$root.$on('cat-modal-close', () => {
                this.catAddModal = false;
            });

            this.$root.$on('category-created', () => {
                this.catAddModal = false;

                this.catData.title = 'Add New';
                this.catData.node = null;

                this.$swal({
                    position: 'center',
                    type: 'success',
                    title: 'Successful!',
                    showConfirmButton: false,
                    timer: 1500
                });

                this.fetchLedgerCategories();
            });
        },

        methods: {
            categoryAddModal() {
                this.catAddModal = true;
            },

            buildTree(elements, parentId = null) {
                let branch = [];

                elements.forEach(element => {
                    if ( element['parent_id'] === parentId ) {
                        let children = this.buildTree(elements, element.id);

                        if ( children.length ) {
                            element['children'] = children;
                        }

                        branch.push(element);
                    }
                });

                return branch;
            },

            fetchChartAccounts() {
                this.chartAccounts = [];

                HTTP.get('/ledgers/accounts').then( response => {
                    this.chartAccounts = response.data;
                });
            },

            fetchLedgerCategories() {
                HTTP.get('/ledgers/categories').then( response => {
                    if ( ! response.data ) return;

                    this.categories = this.buildTree( response.data );
                });
            },

            editCategory(node) {
                this.catData.title = 'Update';
                this.catData.node = node;

                this.catAddModal = true;
            },

            removeCategory(node) {
                if ( confirm('Are you sure to remove this category?') ) {
                    HTTP.delete(`/ledgers/categories/${node.id}`).then(response => {
                        this.$swal({
                            position: 'center',
                            type: 'error',
                            title: 'Category Removed!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        this.fetchLedgerCategories();
                    });
                }
            },

            saveAccount() {
                this.error = false;
                this.isChartAdding = true;

                HTTP.post('/ledgers', {
                    chart_id: this.ledgFields.chart_id,
                    category_id: this.ledgFields.category_id,
                    name: this.ledgFields.name,
                    code: this.ledgFields.code
                }).then(response => {
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Success!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).catch((err) => {
                    // Error message
                    this.error = err.response.data.message;

                }).then(() => {
                    this.ledgFields.chart_id = null;
                    this.ledgFields.category_id = null;
                    this.ledgFields.name = '';
                    this.ledgFields.code = '';
                    this.isChartAdding = false;
                });
            }
        }
    }
</script>

<style lang="less">
    .vue-treeselect--single {
        .vue-treeselect__input,
        .vue-treeselect__input:focus {
            padding: 0;
            border: 0;
            box-shadow: none;
            height: auto !important;
        }
    }

    .after-select-dropdown {
        padding-top: 5px;
        display: inline-block;
        font-size: 12px;
        text-decoration: underline;
    }

    .list-actions {
        float: right;

        .edit {
            color: #1976d2;
        }

        .remove {
            color: #b71c1c;
        }
    }

    .chart-accounts {
        width: 350px;

        .form-row {
            padding-bottom: 20px;

            & > label {
                font-weight: bold;
            }
        }
    }
</style>
