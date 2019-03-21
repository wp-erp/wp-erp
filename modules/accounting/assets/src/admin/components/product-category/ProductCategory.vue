<template>
    <div>
        <div class="categories">
            <div id="col-left">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <h2>Add new category</h2>
                        <form action="" method="POST" id="product-category">
                            <div :class="['form-field term-name-wrap', { 'form-invalid': error }]">
                                <label for="">Name</label>
                                <input type="text" v-model="categoryName">
                                <p>The name is how it appears on your site.</p>
                            </div>
                            <div class="form-field">
                                <label for="">Parent Category</label>
                                <div class="with-multiselect">
                                    <multi-select
                                    v-model="parentCategory"
                                    :options="categories"
                                    :multiple="false" />
                                    <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                </div>
                            </div>
                            <div class="buttons-wrapper text-right">
                                <input type="submit" value="Add new category" class="wperp-btn btn--primary" @click.prevent="createCategory"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="col-right" style="margin-top: 12px">
                <div class="col-wrap">
                    <list-table class="category-list"
                        action-column="actions"
                        :columns="columns"
                        :rows="categories"
                        :actions="[
                            { key: 'edit', label: 'Edit' },
                            { key: 'trash', label: 'Delete' }
                        ]"
                        :bulk-actions="bulkActions"
                        @action:click="onActionClick"
                        @bulk:click="onBulkAction">
                        >
                        <template slot="name" slot-scope="data" v-if="data.row.isEdit" >
                            <input type="text" :value="data.row.name" :id="'cat-'+data.row.id">
                            <div class="buttons-wrapper text-right" style="margin-top: 10px">
                                <button class="wperp-btn btn--primary" @click="updateCategory(data.row)">Update</button>
                                <button class="wperp-btn btn--default" @click.prevent="data.row.isEdit = false">Cancel</button>
                            </div>
                        </template>
                    </list-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http.js'
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {
        name: 'ProductCategory',

        components: {
            ListTable,
            MultiSelect
        },

        data() {
            return {
                categories:[],
                categoryName: '',
                parentCategory: 0,
                category: null,
                error: false,
                showModal: false,
                columns: {
                    'name': {
                        label: 'Category Name'
                    },
                    'actions': { label: 'Actions' }
                },
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png',
                    }
                ],
            }
        },

        created() {
            this.$store.dispatch( 'spinner/setSpinner', true );
            this.getCategories();
            this.$on( 'close', function() {
                this.showModal = false;
            } );
        },
        methods: {
            getCategories() {
                HTTP.get('product-cats')
                    .then( (response) => {
                        let categories = response.data
                        for ( let x in categories ) {
                            let category = categories[x];
                            let object = { id: category.id, name: category.name, isEdit: false };
                            this.categories.push( object );
                        }
                        this.$store.dispatch( 'spinner/setSpinner', false );
                    } )
                    .catch( (error) => {
                        this.$store.dispatch( 'spinner/setSpinner', false );
                    } )
            },

            onActionClick( action, row, index ) {
                if ( 'edit' == action ) {
                    row.isEdit = true;
                    this.category = row;
                } else if ( 'trash' == action ) {
                    if ( confirm( "Are you sure want to delete ?" ) ) {
                        this.$store.dispatch( 'spinner/setSpinner', true );
                        HTTP.delete( 'product-cats/' + row.id )
                        .then( (response) => {
                            this.$delete( this.categories, index );

                            this.$store.dispatch( 'spinner/setSpinner', false );
                            this.showAlert( 'success', 'Deleted!' );
                        } ).catch( error => {
                            this.$store.dispatch( 'spinner/setSpinner', false );
                        } );
                    }
                }
            },
            onBulkAction( action, items ) {
                if ( 'trash' == action ) {
                    if ( confirm( 'Are you sure want to delete?' ) ) {
                        this.$store.dispatch( 'spinner/setSpinner', true );

                        HTTP.delete('product-cats/delete/' + items).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if ( toggleCheckbox.checked ) {
                                toggleCheckbox.click();
                            }
                            this.categories = this.categories.filter( item => {
                                return items.indexOf( item.id ) == -1;
                            } );
                            this.$store.dispatch( 'spinner/setSpinner', false );

                        }).catch( error => {
                            this.$store.dispatch( 'spinner/setSpinner', false );
                        } );
                    }
                }
            },

            createCategory() {
                if ( this.categoryName == '' ) {
                    this.error = true;
                    return;
                }

                this.$store.dispatch( 'spinner/setSpinner', true );
                var data = {
                    name: this.categoryName,
                    parent: this.parentCategory
                };
                HTTP.post( 'product-cats', data )
                    .then( (response) => {
                        this.categories.push( response.data );
                        this.categoryName = '';
                        this.parentCategory = 0;

                        this.$store.dispatch( 'spinner/setSpinner', false );
                    } )
                    .catch( ( error ) => {
                        this.$store.dispatch( 'spinner/setSpinner', false );
                    } )
            },

            updateCategory( row ) {
                var categoryName = document.getElementById('cat-'+row.id).value;
                var categoryId   = row.id;

                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.put( 'product-cats/' + categoryId, { name: categoryName } )
                    .then( (response) => {
                        row.isEdit = false;
                        row.name = categoryName;

                        this.$store.dispatch( 'spinner/setSpinner', false );
                    } ).catch( error => {
                        this.$store.dispatch( 'spinner/setSpinner', false );
                    } );
            },

        }
    }
</script>

<style lang="less">
    .categories {
        .category-list {
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
        }
        .buttons-wrapper {
            float: left !important;
        }
        .with-multiselect {
            width: 60%;
        }
    }
</style>
