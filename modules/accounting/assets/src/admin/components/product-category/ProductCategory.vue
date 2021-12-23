<template>
    <div>
        <div class="categories">
            <div id="col-left">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <h2>{{ __('Add new category', 'erp') }}</h2>
                        <form id="erp-acct-product-category">
                            <div :class="['form-field term-name-wrap', { 'form-invalid': error }]">
                                <label>{{ __('Category Name', 'erp') }}</label>
                                <input type="text" class="wperp-form-field" v-model="categoryName">
                            </div>
                            <div class="form-field">
                                <label>{{ __('Parent Category', 'erp') }}</label>
                                <div class="with-multiselect">
                                    <multi-select
                                        v-model="parentCategory"
                                        :options="categories"
                                        :multiple="false"/>
                                    <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                </div>
                            </div>
                            <div class="buttons-wrapper">
                                <input type="submit" :value="__('Save', 'erp')" class="wperp-btn btn--primary text-left"
                                       @click.prevent="createCategory"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="col-right" style="margin-top: 12px">
                <div class="col-wrap">
                    <list-table class="wperp-table table-striped table-dark widefat table2 category-list"
                                action-column="actions"
                                :columns="columns"
                                :rows="categories"
                                :actions="actions"
                                :bulk-actions="bulkActions"
                                :showCb="false"
                                @action:click="onActionClick"
                                @bulk:click="onBulkAction">
                        >
                        <template slot="name" slot-scope="data" v-if="data.row.isEdit">
                            <input type="text" class="wperp-form-field" :value="data.row.name" :id="'cat-'+data.row.id">
                           <!-- <multi-select
                                v-model="data.row.parent"
                                :options="categories"
                                :multiple="false"/>-->
                            <div class="buttons-wrapper text-right" style="margin-top: 10px">
                                <button class="wperp-btn btn--primary" @click="updateCategory(data.row)">{{ __('Update', 'erp') }}</button>
                                <button class="wperp-btn btn--default" @click.prevent="data.row.isEdit = false">{{ __('Cancel', 'erp') }}
                                </button>
                            </div>
                        </template>
                    </list-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http.js';
import ListTable from 'admin/components/list-table/ListTable.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'ProductCategory',

    components: {
        ListTable,
        MultiSelect
    },

    data() {
        return {
            categories    : [],
            categoryName  : '',
            parentCategory: 0,
            category      : null,
            error         : false,
            showModal     : false,
            columns       : {
                name   : {
                    label: __('Category Name', 'erp'),
                    isColPrimary: true
                },
                actions: {
                    label: __('Actions', 'erp')
                }
            },
            actions       : [
                { key: 'edit', label: __('Edit', 'erp') },
                { key: 'trash', label: __('Delete', 'erp') }
            ],
            bulkActions   : [
                {
                    key  : 'trash',
                    label: __('Move to Trash', 'erp'),
                    img  : erp_acct_var.erp_assets + '/images/trash.png' /* global erp_acct_var */
                }
            ]
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.getCategories();
        this.$on('close', function() {
            this.showModal = false;
        });
    },
    methods: {
        getCategories() {
            HTTP.get('product-cats').then((response) => {
                const categories = response.data;
                for (const x in categories) {
                    const category = categories[x];
                    const object   = { id: category.id, name: category.name, isEdit: false };
                    this.categories.push(object);
                }
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch((error) => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        onActionClick(action, row, index) {
            if (action === 'edit') {
                row.isEdit    = true;
                this.category = row;
            } else if (action === 'trash') {
                if (confirm(__('Are you sure want to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete('product-cats/' + row.id).then((response) => {
                        this.$delete(this.categories, index);

                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', __('Deleted !', 'erp'));
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
            }
        },

        onBulkAction(action, items) {
            if (action === 'trash') {
                if (confirm(__('Are you sure want to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);

                    HTTP.delete('product-cats/delete/' + items).then(response => {
                        const toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                        if (toggleCheckbox.checked) {
                            toggleCheckbox.click();
                        }
                        this.categories = this.categories.filter(item => {
                            return items.indexOf(item.id) === -1;
                        });
                        this.$store.dispatch('spinner/setSpinner', false);
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
            }
        },

        createCategory() {
            if (this.categoryName === '') {
                this.error = true;
                return;
            }

            this.$store.dispatch('spinner/setSpinner', true);
            var data = {
                name  : this.categoryName,
                parent: this.parentCategory
            };
            HTTP.post('/product-cats', data).then((response) => {
                this.categories.push(response.data);
                this.categoryName   = '';
                this.parentCategory = 0;

                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', __('Product category added!', 'erp'));
            }).catch((error) => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        updateCategory(row) {
            var categoryName = document.getElementById('cat-' + row.id).value;
            var categoryId   = row.id;

            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.put('/product-cats/' + categoryId, { name: categoryName }).then((response) => {
                row.name = categoryName;

                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', __('Product category updated!', 'erp'));
            }).catch(error => {
                row.isEdit = false;
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        }

    }
};
</script>

<style lang="less">
    .categories {
        .category-list {
            background-color: transparent;

            th ul,
            th li {
                margin: 0;
            }

            th li {
                display: flex;
                align-items: center;

                img {
                    width: 20px;
                    padding-right: 5px;
                }
            }

            .name {
                width: 80% !important;
            }

            .check-column {
                padding: 20px !important;
            }


            @media (min-width: 783px) {
                .col--actions {
                    float: left !important;
                }
                .row-actions {
                    text-align: left !important;
                }
            }
        }

        .buttons-wrapper .wperp-btn {
            margin-left: 0 !important;
        }

        .with-multiselect {
            width: 60%;
        }
    }
</style>
