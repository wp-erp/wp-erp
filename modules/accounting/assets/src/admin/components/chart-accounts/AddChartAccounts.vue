<template>
<div class="wperp-container">

        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ editMode ? 'Update' : 'Create New' }} Account</h2>
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
                        <!-- <span class="list-actions" v-if="node.raw.system == null">
                            <strong class="edit" @click.prevent="editCategory(node)">&#9998;</strong>
                            <strong class="remove" @click.prevent="removeCategory(node)">&cross;</strong>
                        </span> -->
                    </label>
                </treeselect>

                <!-- <a href="#" @click.prevent="categoryAddModal" role="button" class="after-select-dropdown">Add new category</a> -->
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
                {{ editMode ? 'Update' : 'Save' }}
            </button>
        </form>

        <!-- <cat-add-modal v-if="catAddModal" :categories="categories" :catData="catData" /> -->

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
                categories   : [],
                catAddModal  : false,

                ledgFields: {
                    chart_id    : null,
                    category_id : null,
                    name: '',
                    code        : ''
                },

                catData: {
                    title: 'Add New',
                    node : null
                },

                ledgerID     : 0,
                editMode     : false,
                error        : false,
                isChartAdding: false,
            };
        },

        components: {
            Treeselect,
            CatAddModal,
        },

        watch: {
            'ledgFields.chart_id'() {
                this.fetchLedgerCategories();
            }
        },

        created() {
            this.prepareDataLoad();

            // this.$root.$on('cat-modal-close', () => {
            //     this.catAddModal = false;
            // });

            // this.$root.$on('category-created', () => {
            //     this.catAddModal = false;

            //     this.catData.title = 'Add New';
            //     this.catData.node = null;

            //     this.showAlert('success', 'Successful !');

            //     this.fetchLedgerCategories();
            // });
        },

        methods: {
            async prepareDataLoad() {
                /**
                 * ----------------------------------------------
                 * check if editing
                 * -----------------------------------------------
                 */
                if ( this.$route.params.id ) {
                    this.editMode = true;
                    this.ledgerID = this.$route.params.id;

                    /**
                     * Duplicates of
                     *? this.fetchChartAccounts()
                     *? this.fetchLedgerCategories()
                     * load accounts and categories, before ledger load
                     */
                    let [request1, request2] = await Promise.all([
                        HTTP.get('/ledgers/accounts'),
                        HTTP.get(`/ledgers/${this.$route.params.id}`)
                    ]);
                    let request3 = await HTTP.get(`/ledgers/categories/${request2.data.chart_id}`);

                    this.chartAccounts = request1.data;
                    this.setDataForEdit( request2.data );
                    this.categories = this.buildTree( request3.data );

                } else {
                    /**
                     * ----------------------------------------------
                     * create a new ledger
                     * -----------------------------------------------
                     */
                    this.fetchChartAccounts();
                    this.fetchLedgerCategories();
                }
            },

            setDataForEdit(ledger) {
                this.ledgFields.chart_id    = ledger.chart_id;
                this.ledgFields.name        = ledger.name;
                this.ledgFields.category_id = ledger.category_id;
                this.ledgFields.code        = ledger.code;
            },

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
                if ( ! this.ledgFields.chart_id ) return;

                HTTP.get(`/ledgers/categories/${this.ledgFields.chart_id}`).then( response => {
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
                        this.showAlert('error', 'Category Removed!');

                        this.fetchLedgerCategories();
                    });
                }
            },

            createLedger(requestData) {
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.post('/ledgers', requestData).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert('success', 'Created !');
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
                    this.resetFields();
                });
            },

            updateteLedger(requestData) {
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.put(`/ledgers/${this.ledgerID}`, requestData).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert('success', 'Updated !');
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
                    this.resetFields();
                    this.$router.push({name: 'ChartOfAccounts'});
                });
            },

            saveAccount() {
                this.$store.dispatch( 'spinner/setSpinner', true );

                this.error         = false;
                this.isChartAdding = true;

                let requestData = {
                    chart_id   : this.ledgFields.chart_id,
                    category_id: this.ledgFields.category_id,
                    name       : this.ledgFields.name,
                    code       : this.ledgFields.code
                };

                if ( this.editMode ) {
                    this.updateteLedger(requestData);
                } else {
                    this.createLedger(requestData);
                }
            },

            resetFields() {
                this.ledgFields.chart_id    = null;
                this.ledgFields.category_id = null;
                this.ledgFields.name        = '';
                this.ledgFields.code        = '';
                this.isChartAdding          = false;

                this.$store.dispatch( 'spinner/setSpinner', false );
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

        input[type="number"] {
            height: auto;
        }
    }
</style>

<style lang="less" scoped>

</style>
