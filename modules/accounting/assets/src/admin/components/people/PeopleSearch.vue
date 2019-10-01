<template>
    <div class="people-search">
        <h4>Search</h4>

        <form @submit.prevent="searchPeople">
            <div class="input-with-addon">
                <input v-model="search" type="search" class="wperp-form-field">
                <span v-if="search" @click="clearSearch">&#x2716;</span>
            </div>

            <button type="submit" class="wperp-btn btn--primary">Search</button>
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
    width: 50%;
    align-items: center;
    justify-content: flex-end;

    form {
        display: flex
    }

    h4 {
        margin: 0;
    }

    .input-with-addon {
        position: relative;
        margin: 0 15px;

        input {
            width: 200px;
        }

        span {
            position: absolute;
            right: 5px;
            top: 10px;
            font-size: 10px;
            cursor: pointer;
        }
    }
}
</style>
