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
            <div class="form-row">
                <label for="">Select chart of accounts</label>
                 <treeselect v-model="fields.chart_id"
                    :options="chartAccounts"
                    :disable-branch-nodes="true"
                    :show-count="true"
                    placeholder="Please select" />
            </div>

            <div class="form-row">
                <label for="">Select Category (optional)</label>
                <treeselect v-model="fields.category_id"
                    :options="categories"
                    :disable-branch-nodes="true"
                    :show-count="true"
                    placeholder="Please select a category">

                    <label slot="option-label" slot-scope="{ node, shouldShowCount, count, labelClassName, countClassName }" :class="labelClassName">
                        {{ node.label }}
                        <span v-if="shouldShowCount" :class="countClassName">({{ count }})</span>
                        <span class="list-actions">
                            <strong class="edit" @click.prevent="editCategory(node)">&#9998;</strong>
                            <strong class="remove" @click.prevent="removeCategory(node)">&cross;</strong>
                        </span>
                    </label>

                </treeselect>

                <a href="#" @click.prevent="categoryAddModal" role="button" class="after-select-dropdown">Add new category</a>
            </div>

            <div class="form-row">
                <label for="">Account Name</label>

                <input type="text" v-model="fields.name" required>
            </div>

            <div class="form-row">
                <label for="">Code (optional)</label>

                <input type="number" v-model="fields.code">
            </div>

            <button class="wperp-btn btn--primary" type="submit" @click.prevent="saveAccount">Save</button>
        </form>

        <cat-add-modal v-if="catAddModal" :categories="categories" />

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

                fields: {
                    chart_id: null,
                    category_id: null,
                    account_name: '',
                    code: ''
                }
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
                this.$swal({
                    position: 'center',
                    type: 'success',
                    title: 'Category Created!',
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
                console.log(node);
            },

            removeCategory(node) {
                if ( confirm('Are you sure to remove this category?') ) {
                    // 
                }
            },

            saveAccount() {
                HTTP.post('/ledgers', {
                    chart_id: fields.chart_id,
                    category_id: fields.category_id,
                    name: fields.name,
                    code: fields.code
                }).then(response => {
                    
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
