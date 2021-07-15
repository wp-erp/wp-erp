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

<style lang="less" scoped>
.product-search {
    display: flex;
    position: absolute;
    width: 20%;
    align-items: center;
    justify-content: flex-end;
    right: 1.25rem;

    form {
        display: flex
    }

    h4 {
        margin: 0;
    }

    @media (max-width: 782px) {
        right: 0.8rem;
        margin-top: 9px;
    }

    .input-with-addon {
        position: relative;

        @media (max-width: 782px) {
            margin-top: 14px;
            height: 29.8px;
            max-height: 29.8px;
        }

        input {
            width: 180px;
            max-height: 25px;
            height: 25px;
            border: 0.3px solid rgb(226, 226, 226);

            &::placeholder {
                color: rgba(0,0,0,0.6) !important;
                font-size: 12px !important;
                font-weight: 400 !important;
            }

            @media (max-width: 782px) {
                height: 30px;
                min-height: 30px;
            }

             @media (max-width: 650px) {     
                width: 120px;
            }
        }

        span {
            position: absolute;
            right: 7px;
            top: 4px;
            font-size: 15px;
            cursor: pointer;
        }
    }

    .search-btn {
        @media (max-width: 782px) {
            margin-top: 14px!important;
        }

        @media (max-width: 650px) {
            display: none;
        }
    }
}
</style>
