<template>
    <div class="product-search">
        <form @submit.prevent="searchPeople">
            <div class="input-with-addon">
                <input v-model="search" type="search" class="wperp-form-field" :placeholder="__('Search Products', 'erp')">
                <span v-if="search" @click="clearSearch">&#x2716;</span>
            </div>
            
            <button type="submit" class="wperp-btn btn--primary search-btn">{{ __('Search', 'erp') }}</button>
        </form>
    </div>
</template>

<script>
export default {
    name: 'ProductSearch',

    data() {
        return {
            search: ''
        };
    },

    methods: {
        searchPeople() {
            this.$emit('input', this.search);
        },

        clearSearch() {
            this.search = '';
            this.searchPeople();
        }
    }
};
</script>