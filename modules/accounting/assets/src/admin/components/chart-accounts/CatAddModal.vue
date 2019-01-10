<template>
    <div class="wperp-modal wperp-modal-open wperp-invoice-modal wperp-custom-scroll" role="dialog">
        <div class="wperp-modal-dialog" v-click-outside="outside" @click="inside">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <h4>Add New Category</h4>
                </div>
                <div class="wperp-modal-body">
                    <form action="" class="ledger-cat-form" @submit.prevent="saveCategory">

                        <div class="form-row">
                            <label for="">Parent Category (optional)</label>
                            <treeselect v-model="parent"
                                :options="categories"
                                :disable-branch-nodes="true"
                                :show-count="true"
                                placeholder="Please select a category" />
                        </div>

                        <div class="form-row">
                            <label for="">Name of Category</label>

                            <input type="text" v-model="category" required>
                        </div>

                        <div class="wperp-modal-footer pt-0">
                            <div class="buttons-wrapper text-right">
                                <button class="wperp-btn btn--default modal-close" @click.prevent="outside">Cancel</button>
                                <button class="wperp-btn btn--primary" type="submit">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import Treeselect from '@riophae/vue-treeselect';

    export default {
        name: 'CatAddModal',

        data() {
            return {
                parent: null,
                category: '',
            }
        },

        props: {
            categories: {
                type: Array,
            }
        },
        
        components: {
            Treeselect
        },

        created() {
            //
        },

        methods: {
            inside() {},

            outside() {
                this.$root.$emit('cat-modal-close');
            },

            saveCategory() {                
                HTTP.post('/ledgers/categories', {
                    parent: this.parent,
                    name: this.category
                }).then(response => {
                    this.parent = null;
                    this.category = '';

                    this.$root.$emit('category-created');
                });
            }
        },

    }
</script>

<style lang="less" scoped>
    .ledger-cat-form {
        padding-top: 20px;
        min-height: 300px;

        .form-row {
            padding-bottom: 20px;
        }

        .buttons-wrapper {
            padding-top: 20px;
        }
    }
</style>
