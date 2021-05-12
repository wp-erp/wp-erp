<template>
    <div class="people-search">
        <form @submit.prevent="searchPeople">
            <div class="input-with-addon">
                <input v-model="search" type="search" class="wperp-form-field" :placeholder="__('Search People', 'erp')">
                <span v-if="search" @click="clearSearch">&#x2716;</span>
            </div>
            
            <button type="submit" class="wperp-btn btn--primary search-btn">{{ __('Search', 'erp') }}</button>
        </form>
    </div>
</template>

<script>
export default {
    name: 'PeopleSearch',

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
.people-search {
    display: flex;
    position: fixed;
    width: 25%;
    align-items: center;
    justify-content: flex-end;
    right: 1.25rem;

    form {
        display: flex
    }

    h4 {
        margin: 0;
    }

    .input-with-addon {
        position: relative;
        margin-top: 12px;

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
        }

        span {
            position: absolute;
            right: 5px;
            top: 10px;
            font-size: 10px;
            cursor: pointer;
        }
    }

    .search-btn {
        margin-top: 12px !important;
    }
}
</style>
